# Plan Implementacji: System Generowania Faktur PDF

**Status:** 📋 ZAPLANOWANE (oczekuje na budżet)
**Wycena:** 1,700 PLN (17h × 100 PLN/h)
**Timeline:** 10 dni roboczych
**Priorytet:** Średni

---

## Podsumowanie

Implementacja automatycznego generowania faktur VAT w formacie PDF dla rezerwacji z danymi do faktury. Faktury generowane on-the-fly (bez zapisu na dysku), dostępne dla klientów w panelu `/my-appointments` oraz dla adminów/staff w Filament.

**Główne komponenty**:
- PDF Engine: Spatie Laravel-PDF (Puppeteer) z fallbackiem mPDF
- Invoice numbering: FV/YYYY/MM/XXXX (sequential per month)
- VAT: 23% (ceny w Service są brutto)
- Authorization: Policy-based (customer/admin/assigned staff)
- Security: Rate limiting, UUID filenames, input validation

---

## 1. Database Changes

### Migration 1: Add price snapshot to appointments

**File**: `database/migrations/YYYY_MM_DD_add_price_to_appointments.php`

```php
Schema::table('appointments', function (Blueprint $table) {
    $table->decimal('price', 10, 2)->nullable()->after('invoice_requested')
        ->comment('Service price snapshot (brutto with 23% VAT)');
});
```

**Reasoning**: Cena musi być snapshowana przy booking, żeby zmiany cen usług nie wpływały na historyczne faktury.

### Migration 2: Backfill prices for existing appointments

**File**: `database/migrations/YYYY_MM_DD_backfill_appointment_prices.php`

```sql
UPDATE appointments a
INNER JOIN services s ON a.service_id = s.id
SET a.price = s.price
WHERE a.price IS NULL
```

---

## 2. Configuration

### New config file: `config/invoice.php`

```php
return [
    'number_format' => [
        'prefix' => 'FV',
        'separator' => '/',
        'sequence_padding' => 4,  // FV/2025/12/0001
    ],
    'vat_rate' => 23,  // Poland standard
    'pdf' => [
        'engine' => env('INVOICE_PDF_ENGINE', 'spatie-pdf'),
        'format' => 'A4',
        'timeout' => 60,
        'memory_limit' => '256M',
    ],
    'rate_limit' => [
        'max_attempts' => 10,
        'decay_minutes' => 1,
    ],
];
```

### Environment variables

**File**: `.env`

```bash
INVOICE_PDF_ENGINE=spatie-pdf  # or: mpdf
```

---

## 3. Models

### Update: `app/Models/Appointment.php`

**Add to $fillable**:
```php
'price',
```

**Add to $casts**:
```php
'price' => 'decimal:2',
```

**Add accessor for invoice number**:
```php
public function getInvoiceNumberAttribute(): ?string
{
    if (!$this->invoice_requested) return null;

    return app(\App\Services\Invoice\InvoiceNumberGenerator::class)
        ->generate($this);
}
```

**Add price breakdown method**:
```php
public function getPriceBreakdown(): array
{
    $brutto = (float) $this->price;
    $vatRate = config('invoice.vat_rate');
    $netto = round($brutto / (1 + $vatRate / 100), 2);
    $vat = round($brutto - $netto, 2);

    return [
        'brutto' => $brutto,
        'netto' => $netto,
        'vat' => $vat,
        'vat_rate' => $vatRate,
    ];
}
```

**Update booted() method** (add price snapshot):
```php
protected static function booted(): void
{
    // Existing event listeners...

    // NEW: Snapshot price at booking
    static::creating(function (Appointment $appointment) {
        if (is_null($appointment->price) && $appointment->service) {
            $appointment->price = $appointment->service->price;
        }
    });
}
```

---

## 4. Business Logic

### New DTO: `app/DataTransferObjects/InvoiceData.php`

```php
class InvoiceData
{
    public function __construct(
        public readonly string $invoiceNumber,
        public readonly Carbon $issueDate,
        public readonly Carbon $serviceDate,
        // Seller (company from Settings)
        public readonly string $sellerName,
        public readonly string $sellerNip,
        public readonly string $sellerAddress,
        // ... more seller fields
        // Buyer (customer from Appointment.invoice_*)
        public readonly string $buyerName,
        public readonly ?string $buyerNip,
        public readonly string $buyerAddress,
        // Service
        public readonly string $serviceName,
        public readonly float $servicePriceBrutto,
        public readonly float $servicePriceNetto,
        public readonly float $serviceVat,
        public readonly int $serviceVatRate,
        public readonly int $quantity,
        // Totals
        public readonly float $totalNetto,
        public readonly float $totalVat,
        public readonly float $totalBrutto,
    ) {}

    public static function fromAppointment(Appointment $appointment): self
    {
        $settings = app(SettingsManager::class);
        $priceBreakdown = $appointment->getPriceBreakdown();

        return new self(
            invoiceNumber: $appointment->invoice_number,
            issueDate: $appointment->start_time,
            serviceDate: $appointment->start_time,
            sellerName: $settings->get('invoice.company_name'),
            sellerNip: $settings->get('invoice.company_nip'),
            // ... populate all fields
        );
    }
}
```

### New Service: `app/Services/Invoice/InvoiceNumberGenerator.php`

```php
class InvoiceNumberGenerator
{
    public function generate(Appointment $appointment): string
    {
        $year = $appointment->start_time->format('Y');
        $month = $appointment->start_time->format('m');
        $sequence = $this->getNextSequence($year, $month);

        return "FV/{$year}/{$month}/" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    private function getNextSequence(string $year, string $month): int
    {
        return Cache::lock("invoice_lock_{$year}_{$month}", 10)
            ->block(5, function () use ($year, $month) {
                $count = DB::table('appointments')
                    ->whereYear('start_time', $year)
                    ->whereMonth('start_time', $month)
                    ->where('invoice_requested', true)
                    ->count();

                return $count + 1;
            });
    }
}
```

### New Service: `app/Services/Invoice/InvoicePdfGenerator.php`

```php
class InvoicePdfGenerator
{
    public function generate(Appointment $appointment)
    {
        $invoiceData = InvoiceData::fromAppointment($appointment);
        $filename = $this->generateFilename($invoiceData);

        $engine = config('invoice.pdf.engine');

        return match ($engine) {
            'spatie-pdf' => $this->generateWithSpatie($invoiceData, $filename),
            'mpdf' => $this->generateWithMpdf($invoiceData, $filename),
        };
    }

    private function generateWithSpatie($data, $filename)
    {
        return Pdf::view('pdf.invoice', ['invoice' => $data])
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->name($filename)
            ->download();
    }

    private function generateFilename(InvoiceData $data): string
    {
        $number = str_replace('/', '-', $data->invoiceNumber);
        $uuid = Str::uuid();
        return "invoice_{$number}_{$uuid}.pdf";
    }
}
```

---

## 5. Authorization

### Update: `app/Policies/AppointmentPolicy.php`

```php
public function downloadInvoice(?User $user, Appointment $appointment): bool
{
    if (!$user) return false;
    if (!$appointment->invoice_requested) return false;
    if (is_null($appointment->price)) return false;

    // Customer owns invoice
    if ($user->id === $appointment->customer_id) return true;

    // Admin can download any
    if ($user->hasRole('admin')) return true;

    // Assigned staff
    if ($user->hasRole('staff') && $user->id === $appointment->staff_id) {
        return true;
    }

    return false;
}
```

---

## 6. Controllers & Routes

### New Controller: `app/Http/Controllers/InvoiceController.php`

```php
class InvoiceController extends Controller
{
    public function __construct(
        private InvoicePdfGenerator $pdfGenerator
    ) {
        $this->middleware(['auth', 'throttle:invoice']);
    }

    public function download(Appointment $appointment)
    {
        $this->authorize('downloadInvoice', $appointment);
        $appointment->load(['service', 'customer']);

        return $this->pdfGenerator->generate($appointment);
    }
}
```

### Update: `routes/web.php`

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/appointments/{appointment}/invoice/download',
        [InvoiceController::class, 'download'])
        ->name('appointments.invoice.download');
});
```

### Update: `app/Providers/RouteServiceProvider.php`

```php
RateLimiter::for('invoice', function (Request $request) {
    return Limit::perMinute(10)
        ->by($request->user()?->id ?: $request->ip());
});
```

---

## 7. Filament Admin

### Update: `app/Filament/Pages/SystemSettings.php`

**Add invoiceTab() method**:
```php
private function invoiceTab(): Tabs\Tab
{
    return Tabs\Tab::make('Faktury')
        ->schema([
            Section::make('Dane firmy')
                ->schema([
                    TextInput::make('invoice.company_name')
                        ->label('Nazwa firmy')->required(),
                    TextInput::make('invoice.company_nip')
                        ->label('NIP')->maxLength(20),
                    TextInput::make('invoice.company_regon')
                        ->label('REGON')->maxLength(20),
                    // ... more fields
                ]),
            Section::make('Adres')
                ->schema([
                    TextInput::make('invoice.address_line'),
                    TextInput::make('invoice.postal_code'),
                    TextInput::make('invoice.city'),
                ]),
            Section::make('Kontakt')
                ->schema([
                    TextInput::make('invoice.phone')->tel(),
                    TextInput::make('invoice.email')->email(),
                    TextInput::make('invoice.bank_account'),
                ]),
        ]);
}
```

**Update tabs array in form()**:
```php
->tabs([
    $this->bookingTab(),
    $this->mapTab(),
    $this->contactTab(),
    $this->invoiceTab(),  // NEW
    // ... rest
])
```

### Update: `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php`

```php
protected function getHeaderActions(): array
{
    return [
        Action::make('downloadInvoice')
            ->label('Pobierz fakturę PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->color('success')
            ->visible(fn() => $this->record->invoice_requested &&
                              !is_null($this->record->price))
            ->authorize('downloadInvoice', $this->record)
            ->action(function (InvoicePdfGenerator $generator) {
                $this->record->load(['service', 'customer']);
                return $generator->generate($this->record);
            }),
    ];
}
```

---

## 8. Views

### New Template: `resources/views/pdf/invoice.blade.php`

```blade
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Faktura {{ $invoice->invoiceNumber }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 10px; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FAKTURA VAT</h1>
        <p>{{ $invoice->invoiceNumber }}</p>
    </div>

    <div class="parties">
        <div class="party">
            <h2>Sprzedawca</h2>
            <p>{{ $invoice->sellerName }}</p>
            <p>NIP: {{ $invoice->sellerNip }}</p>
            <p>{{ $invoice->sellerAddress }}</p>
        </div>

        <div class="party">
            <h2>Nabywca</h2>
            <p>{{ $invoice->buyerName }}</p>
            @if($invoice->buyerNip)
                <p>NIP: {{ $invoice->buyerNip }}</p>
            @endif
            <p>{{ $invoice->buyerAddress }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Lp.</th>
                <th>Nazwa</th>
                <th>Ilość</th>
                <th>Cena netto</th>
                <th>VAT</th>
                <th>Wartość brutto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>{{ $invoice->serviceName }}</td>
                <td>1</td>
                <td>{{ number_format($invoice->servicePriceNetto, 2, ',', ' ') }} zł</td>
                <td>23%</td>
                <td>{{ number_format($invoice->servicePriceBrutto, 2, ',', ' ') }} zł</td>
            </tr>
        </tbody>
    </table>

    <div class="totals">
        <p>Suma netto: {{ number_format($invoice->totalNetto, 2, ',', ' ') }} zł</p>
        <p>VAT (23%): {{ number_format($invoice->totalVat, 2, ',', ' ') }} zł</p>
        <p class="total">SUMA BRUTTO: {{ number_format($invoice->totalBrutto, 2, ',', ' ') }} zł</p>
    </div>
</body>
</html>
```

### Update: `resources/views/appointments/index.blade.php`

Add button after existing actions (around line 80):

```blade
@if ($appointment->invoice_requested && !is_null($appointment->price))
    <a href="{{ route('appointments.invoice.download', $appointment) }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg"
       target="_blank">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586..." />
        </svg>
        Pobierz fakturę
    </a>
@endif
```

---

## 9. Settings Seeder

### Update: `database/seeders/SettingSeeder.php`

```php
private function seedInvoiceSettings(): void
{
    $this->createSetting('invoice', 'company_name', 'Paradocks Car Detailing');
    $this->createSetting('invoice', 'company_nip', '');
    $this->createSetting('invoice', 'company_regon', '');
    $this->createSetting('invoice', 'company_vat_id', 'PL');
    $this->createSetting('invoice', 'address_line', '');
    $this->createSetting('invoice', 'city', '');
    $this->createSetting('invoice', 'postal_code', '');
    $this->createSetting('invoice', 'phone', '');
    $this->createSetting('invoice', 'email', '');
    $this->createSetting('invoice', 'bank_account', '');
}

public function run(): void
{
    // ... existing seeders
    $this->seedInvoiceSettings();
}
```

---

## 10. Testing

### Feature Test: `tests/Feature/InvoiceDownloadTest.php`

```php
class InvoiceDownloadTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function customer_can_download_their_own_invoice()
    {
        $customer = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'invoice_requested' => true,
            'price' => 500.00,
        ]);

        $response = $this->actingAs($customer)
            ->get(route('appointments.invoice.download', $appointment));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function customer_cannot_download_other_invoice()
    {
        $customer = User::factory()->create();
        $other = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'customer_id' => $other->id,
            'invoice_requested' => true,
            'price' => 500.00,
        ]);

        $response = $this->actingAs($customer)
            ->get(route('appointments.invoice.download', $appointment));

        $response->assertForbidden();
    }

    /** @test */
    public function rate_limiting_applies()
    {
        $customer = User::factory()->create();
        $appointment = Appointment::factory()->create([
            'customer_id' => $customer->id,
            'invoice_requested' => true,
            'price' => 500.00,
        ]);

        // Exhaust limit
        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($customer)
                ->get(route('appointments.invoice.download', $appointment));
        }

        $response = $this->actingAs($customer)
            ->get(route('appointments.invoice.download', $appointment));

        $response->assertStatus(429);
    }
}
```

### Unit Test: `tests/Unit/InvoiceNumberGeneratorTest.php`

```php
class InvoiceNumberGeneratorTest extends TestCase
{
    /** @test */
    public function generates_correct_format()
    {
        $appointment = Appointment::factory()->create([
            'start_time' => '2025-12-15 10:00:00',
        ]);

        $generator = new InvoiceNumberGenerator();
        $number = $generator->generate($appointment);

        $this->assertMatchesRegularExpression('/^FV\/2025\/12\/\d{4}$/', $number);
    }

    /** @test */
    public function increments_sequence_per_month()
    {
        $appointment1 = Appointment::factory()->create([
            'start_time' => '2025-12-01',
            'invoice_requested' => true,
        ]);
        $appointment2 = Appointment::factory()->create([
            'start_time' => '2025-12-15',
            'invoice_requested' => true,
        ]);

        $generator = new InvoiceNumberGenerator();

        $this->assertEquals('FV/2025/12/0001', $generator->generate($appointment1));
        $this->assertEquals('FV/2025/12/0002', $generator->generate($appointment2));
    }
}
```

---

## 11. Installation

### Dependencies

```bash
# Composer
cd app && composer require spatie/laravel-pdf

# NPM (Puppeteer)
cd app && npm install --save-dev puppeteer

# Fallback (if Puppeteer issues)
composer require mpdf/mpdf
```

### Deployment Steps

```bash
# 1. Install packages
cd app
composer require spatie/laravel-pdf
npm install --save-dev puppeteer

# 2. Run migrations
docker compose exec app php artisan migrate

# 3. Seed invoice settings
docker compose exec app php artisan db:seed --class=SettingSeeder

# 4. Configure in admin panel
# Go to: /admin/system-settings → Faktury tab
# Fill company details

# 5. Clear caches
docker compose exec app php artisan optimize:clear

# 6. Run tests
composer run test
```

---

## 12. Security Checklist

- [x] Policy-based authorization (`AppointmentPolicy::downloadInvoice`)
- [x] Rate limiting (10 req/min via `throttle:invoice`)
- [x] UUID filenames (no path traversal)
- [x] Blade auto-escaping (`{{ }}` not `{!! !!}`)
- [x] No SSRF (remote files disabled in PDF config)
- [x] Input validation (price is decimal, dates are Carbon)
- [x] Memory limit (256M) and timeout (60s)
- [x] Ownership validation (Policy checks customer_id)

---

## 13. Files to Create/Modify

### Create (12 files)

1. `database/migrations/YYYY_MM_DD_add_price_to_appointments.php`
2. `database/migrations/YYYY_MM_DD_backfill_appointment_prices.php`
3. `config/invoice.php`
4. `app/DataTransferObjects/InvoiceData.php`
5. `app/Services/Invoice/InvoiceNumberGenerator.php`
6. `app/Services/Invoice/InvoicePdfGenerator.php`
7. `app/Http/Controllers/InvoiceController.php`
8. `resources/views/pdf/invoice.blade.php`
9. `tests/Feature/InvoiceDownloadTest.php`
10. `tests/Unit/InvoiceNumberGeneratorTest.php`
11. `docs/features/invoice-pdf-system/README.md`
12. `docs/features/invoice-pdf-system/installation.md`

### Modify (11 files)

1. `app/Models/Appointment.php` - Add price field, casts, accessor, booted()
2. `app/Policies/AppointmentPolicy.php` - Add downloadInvoice() method
3. `routes/web.php` - Add invoice download route
4. `app/Providers/RouteServiceProvider.php` - Add rate limiter
5. `app/Filament/Pages/SystemSettings.php` - Add invoiceTab()
6. `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php` - Add header action
7. `resources/views/appointments/index.blade.php` - Add download button
8. `database/seeders/SettingSeeder.php` - Add seedInvoiceSettings()
9. `.env.example` - Add INVOICE_PDF_ENGINE
10. `docs/README.md` - Link to invoice docs
11. `CLAUDE.md` - Add invoice system entry

---

## 14. Effort Estimation

**Complexity breakdown**:
- Database migrations: 1h (simple)
- DTO & Services: 3h (medium complexity)
- PDF template: 2h (HTML/CSS design)
- Controllers & Routes: 1h (standard CRUD)
- Authorization: 1h (policy extension)
- Filament integration: 2h (settings tab + action)
- Testing: 3h (feature + unit tests)
- Documentation: 1h
- Installation & verification: 1h

**Total estimated effort**: 15-18 hours

**Commercial pricing**:
- 17h × 100 PLN/h = 1,700 PLN
- Contingency included in estimate

---

## 15. Next Steps

Po zatwierdzeniu budżetu:

1. **Phase 1**: Database + Config (migrations, config file)
2. **Phase 2**: Business Logic (DTO, services, policy)
3. **Phase 3**: Controllers + Routes
4. **Phase 4**: Filament Integration (settings tab, action)
5. **Phase 5**: Views (PDF template, customer button)
6. **Phase 6**: Testing (feature + unit tests)
7. **Phase 7**: Documentation + Deployment

**Timeline**: 10 dni roboczych (2 tygodnie kalendarzowe)
