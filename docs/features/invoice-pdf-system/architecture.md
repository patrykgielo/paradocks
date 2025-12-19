# Architektura: System Generowania Faktur PDF

**Wersja:** 1.0
**Status:** Zaplanowane
**Ostatnia aktualizacja:** 19 grudnia 2024

---

## Przegląd Systemu

System generowania faktur VAT PDF składa się z 5 głównych warstw:

```
┌─────────────────────────────────────────────────────────┐
│                    PRESENTATION LAYER                    │
│  ┌──────────────────┐         ┌───────────────────────┐ │
│  │  Filament Admin  │         │   Customer Panel      │ │
│  │  (ViewAppointment│         │  (/my-appointments)   │ │
│  │   + Settings)    │         │                       │ │
│  └──────────────────┘         └───────────────────────┘ │
└─────────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────────┐
│                   CONTROLLER LAYER                       │
│              InvoiceController::download()               │
│         (Authorization + Rate Limiting + DI)             │
└─────────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────────┐
│                  BUSINESS LOGIC LAYER                    │
│  ┌──────────────────────────────────────────────────┐  │
│  │         InvoicePdfGenerator::generate()          │  │
│  │                                                  │  │
│  │  ┌─────────────┐  ┌──────────────────────────┐  │  │
│  │  │ InvoiceData │  │ InvoiceNumberGenerator   │  │  │
│  │  │  (DTO)      │  │  (Redis Lock)            │  │  │
│  │  └─────────────┘  └──────────────────────────┘  │  │
│  └──────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────────┐
│                    RENDERING LAYER                       │
│  ┌──────────────────┐         ┌───────────────────────┐│
│  │  Spatie PDF      │   OR    │   mPDF (fallback)     ││
│  │  (Puppeteer)     │         │                       ││
│  └──────────────────┘         └───────────────────────┘│
└─────────────────────────────────────────────────────────┘
                        ▼
┌─────────────────────────────────────────────────────────┐
│                      DATA LAYER                          │
│  ┌─────────────┐  ┌─────────────┐  ┌──────────────────┐│
│  │ Appointment │  │   Service   │  │  SystemSettings  ││
│  │   Model     │  │   Model     │  │  (Invoice data)  ││
│  └─────────────┘  └─────────────┘  └──────────────────┘│
└─────────────────────────────────────────────────────────┘
```

---

## Główne Komponenty

### 1. InvoiceData (DTO)

**Typ:** Data Transfer Object (Readonly)
**Lokalizacja:** `app/DataTransferObjects/InvoiceData.php`
**Odpowiedzialność:** Transformacja danych z Appointment → struktura faktury

**Pola:**
```php
class InvoiceData
{
    // Metadata
    public readonly string $invoiceNumber;   // FV/2025/12/0001
    public readonly Carbon $issueDate;       // Data wystawienia
    public readonly Carbon $serviceDate;     // Data wykonania usługi

    // Seller (sprzedawca - dane z Settings)
    public readonly string $sellerName;      // Paradocks Car Detailing
    public readonly string $sellerNip;       // 8222370339
    public readonly string $sellerRegon;
    public readonly string $sellerAddress;   // Sformatowany adres
    public readonly string $sellerCity;
    public readonly string $sellerPostalCode;
    public readonly string $sellerPhone;
    public readonly string $sellerEmail;
    public readonly string $sellerBankAccount;

    // Buyer (nabywca - dane z Appointment.invoice_*)
    public readonly string $buyerName;       // Firma XYZ lub Jan Kowalski
    public readonly ?string $buyerNip;       // 123-456-78-90
    public readonly ?string $buyerRegon;
    public readonly ?string $buyerVatId;     // EU VAT ID
    public readonly string $buyerAddress;    // Sformatowany adres
    public readonly string $buyerType;       // individual/company/foreign_eu/foreign_non_eu

    // Service (usługa)
    public readonly string $serviceName;     // Detailing Premium
    public readonly float $servicePriceBrutto;  // 500.00 PLN
    public readonly float $servicePriceNetto;   // 406.50 PLN
    public readonly float $serviceVat;          // 93.50 PLN
    public readonly int $serviceVatRate;        // 23%
    public readonly int $quantity;              // 1

    // Totals (sumy)
    public readonly float $totalNetto;
    public readonly float $totalVat;
    public readonly float $totalBrutto;
}
```

**Factory Method:**
```php
public static function fromAppointment(Appointment $appointment): self
{
    $settings = app(SettingsManager::class);
    $priceBreakdown = $appointment->getPriceBreakdown();

    return new self(
        invoiceNumber: $appointment->invoice_number,
        issueDate: now(),
        serviceDate: $appointment->start_time,
        sellerName: $settings->get('invoice.company_name'),
        sellerNip: $settings->get('invoice.company_nip'),
        // ... all fields populated
    );
}
```

**Zalety readonly pattern:**
- Immutable (dane nie mogą być zmienione po utworzeniu)
- Type-safe (IDE autocomplete + strict types)
- Explicit (wszystkie pola widoczne w konstruktorze)

---

### 2. InvoiceNumberGenerator

**Typ:** Service (Singleton)
**Lokalizacja:** `app/Services/Invoice/InvoiceNumberGenerator.php`
**Odpowiedzialność:** Generowanie sekwencyjnych numerów faktur

**Format:** `FV/YYYY/MM/XXXX`
- `FV` - Faktura VAT (prefix)
- `YYYY` - Rok (4 cyfry)
- `MM` - Miesiąc (2 cyfry)
- `XXXX` - Numer sekwencyjny (4 cyfry, padded zeros)

**Przykłady:**
```
FV/2025/12/0001
FV/2025/12/0002
FV/2026/01/0001  (nowy rok, sekwencja resetuje się)
```

**Concurrent Safety (Redis Lock):**
```php
private function getNextSequence(string $year, string $month): int
{
    return Cache::lock("invoice_lock_{$year}_{$month}", 10)
        ->block(5, function () use ($year, $month) {
            // Critical section - tylko 1 proces może wejść jednocześnie
            $count = DB::table('appointments')
                ->whereYear('start_time', $year)
                ->whereMonth('start_time', $month)
                ->where('invoice_requested', true)
                ->count();

            return $count + 1;
        });
}
```

**Problem:** Race condition przy jednoczesnym generowaniu 2 faktur
**Rozwiązanie:** Redis distributed lock (max 10s wait, block for 5s)

**Edge Cases:**
1. **Koniec miesiąca:** Sekwencja resetuje się 1. dnia nowego miesiąca
2. **Koniec roku:** Sekwencja resetuje się w styczniu
3. **Timeout:** Jeśli lock nie zostanie zwolniony w 10s, rzuca exception

---

### 3. InvoicePdfGenerator

**Typ:** Service (Transient)
**Lokalizacja:** `app/Services/Invoice/InvoicePdfGenerator.php`
**Odpowiedzialność:** Generowanie PDF z szablonu Blade

**Flow:**
```
Appointment → InvoiceData → Blade View → PDF Engine → Binary Stream
```

**PDF Engines:**

#### **Primary: Spatie Laravel-PDF (Puppeteer/Browsershot)**
- **Pros:** Najlepsze renderowanie CSS, UTF-8, flexbox/grid support
- **Cons:** Wymaga Node.js + Puppeteer (~200MB), wolniejsze
- **Use case:** Produkcja (gdy Puppeteer działa)

#### **Fallback: mPDF**
- **Pros:** Pure PHP, brak zewnętrznych zależności, szybsze
- **Cons:** Ograniczone CSS support, problemy z flexbox
- **Use case:** Fallback gdy Puppeteer nie działa lub testing

**Configuration:**
```php
// config/invoice.php
'pdf' => [
    'engine' => env('INVOICE_PDF_ENGINE', 'spatie-pdf'),
    'format' => 'A4',
    'timeout' => 60,
    'memory_limit' => '256M',
],
```

**Spatie PDF Example:**
```php
private function generateWithSpatie($data, $filename)
{
    return Pdf::view('pdf.invoice', ['invoice' => $data])
        ->format('A4')
        ->margins(15, 15, 15, 15)
        ->name($filename)
        ->download();  // lub ->save() lub ->inline()
}
```

**Security:**
- **UUID filename:** Prevents path traversal (`invoice_FV-2025-12-0001_550e8400-e29b-41d4-a716.pdf`)
- **No disk storage:** PDF generated in memory, streamed to browser
- **No remote URLs:** Template nie ładuje zewnętrznych zasobów (SSRF protection)

---

### 4. AppointmentPolicy

**Typ:** Authorization Policy
**Lokalizacja:** `app/Policies/AppointmentPolicy.php`
**Odpowiedzialność:** Kontrola dostępu do pobierania faktur

**Zasady:**
```php
public function downloadInvoice(?User $user, Appointment $appointment): bool
{
    // Rule 1: User must be authenticated
    if (!$user) return false;

    // Rule 2: Appointment must have invoice_requested=true
    if (!$appointment->invoice_requested) return false;

    // Rule 3: Price must be snapshoted
    if (is_null($appointment->price)) return false;

    // Rule 4: Customer owns this invoice
    if ($user->id === $appointment->customer_id) return true;

    // Rule 5: Admin can download any invoice
    if ($user->hasRole('admin')) return true;

    // Rule 6: Assigned staff can download
    if ($user->hasRole('staff') && $user->id === $appointment->staff_id) {
        return true;
    }

    // Default: deny
    return false;
}
```

**Access Matrix:**

| User Role | Own Appointment | Other Appointment |
|-----------|-----------------|-------------------|
| **Customer** | ✅ Allow | ❌ Deny |
| **Staff (assigned)** | ✅ Allow | ❌ Deny |
| **Staff (not assigned)** | ❌ Deny | ❌ Deny |
| **Admin** | ✅ Allow | ✅ Allow |
| **Guest** | ❌ Deny | ❌ Deny |

---

### 5. InvoiceController

**Typ:** HTTP Controller
**Lokalizacja:** `app/Http/Controllers/InvoiceController.php`
**Odpowiedzialność:** Obsługa HTTP request/response dla pobierania PDF

**Route:**
```php
GET /appointments/{appointment}/invoice/download
```

**Middleware:**
- `auth` - Wymaga zalogowania
- `throttle:invoice` - Rate limiting (10 req/min)

**Flow:**
```php
public function download(Appointment $appointment)
{
    // Step 1: Authorization (Policy)
    $this->authorize('downloadInvoice', $appointment);

    // Step 2: Eager loading (N+1 prevention)
    $appointment->load(['service', 'customer']);

    // Step 3: Generate PDF
    return $this->pdfGenerator->generate($appointment);
}
```

**Response Headers:**
```
Content-Type: application/pdf
Content-Disposition: attachment; filename="invoice_FV-2025-12-0001_550e8400.pdf"
Content-Length: 43210
```

---

## Data Flow

### Scenariusz 1: Admin pobiera fakturę z Filament

```
1. Admin klika "Pobierz fakturę PDF" w ViewAppointment
   └─> Filament Action triggered

2. Filament Action wywołuje InvoicePdfGenerator::generate()
   └─> Authorization checked (Admin = always true)

3. InvoiceData::fromAppointment($appointment)
   └─> Pobiera dane z Settings (firma)
   └─> Pobiera dane z Appointment (klient, usługa)
   └─> Oblicza VAT breakdown (brutto → netto)

4. InvoiceNumberGenerator::generate($appointment)
   └─> Redis lock: invoice_lock_2025_12
   └─> Query DB: count invoices in December 2025
   └─> Return: FV/2025/12/0123

5. Blade::render('pdf.invoice', ['invoice' => $invoiceData])
   └─> Template engine processes view
   └─> HTML with polskie znaki (UTF-8)

6. Spatie PDF / mPDF converts HTML → PDF binary
   └─> A4 format, margins 15mm

7. Controller returns BinaryFileResponse
   └─> Browser downloads PDF
```

### Scenariusz 2: Customer pobiera fakturę z panelu

```
1. Customer widzi przycisk "Pobierz fakturę" w /my-appointments
   └─> Conditional: @if ($appointment->invoice_requested && $appointment->price)

2. Customer klika link
   └─> GET /appointments/{id}/invoice/download

3. Middleware sprawdza:
   └─> auth: User zalogowany?
   └─> throttle:invoice: Nie przekroczono 10/min?

4. Controller:
   └─> Policy check: $user->id === $appointment->customer_id?

5-7. (jak powyżej - generowanie PDF)

8. Browser otwiera PDF w nowej karcie (target="_blank")
```

---

## Database Schema Changes

### Nowa kolumna: `appointments.price`

```sql
ALTER TABLE appointments
ADD COLUMN price DECIMAL(10,2) NULL
AFTER invoice_requested
COMMENT 'Service price snapshot (brutto with 23% VAT)';
```

**Reasoning:**
- **Snapshot pattern:** Cena musi być zapisana w momencie booking
- **Historical accuracy:** Zmiany w `services.price` nie wpływają na stare faktury
- **Nullable:** Starsze rezerwacje mogą nie mieć ceny (backfill migration)

**Backfill Strategy:**
```sql
UPDATE appointments a
INNER JOIN services s ON a.service_id = s.id
SET a.price = s.price
WHERE a.price IS NULL;
```

**Index (opcjonalny):**
```sql
CREATE INDEX idx_appointments_invoice_requested
ON appointments (invoice_requested, price);
```

---

## Configuration

### `config/invoice.php`

```php
return [
    // Invoice numbering
    'number_format' => [
        'prefix' => 'FV',           // Faktura VAT
        'separator' => '/',
        'sequence_padding' => 4,    // 0001, 0002, ...
    ],

    // VAT settings
    'vat_rate' => 23,  // Poland standard rate

    // PDF engine
    'pdf' => [
        'engine' => env('INVOICE_PDF_ENGINE', 'spatie-pdf'),
        'format' => 'A4',
        'timeout' => 60,            // Max 60s to generate PDF
        'memory_limit' => '256M',   // Prevent memory exhaustion
    ],

    // Rate limiting
    'rate_limit' => [
        'max_attempts' => 10,       // Max 10 downloads per minute
        'decay_minutes' => 1,
    ],
];
```

### Environment Variables

```bash
# .env
INVOICE_PDF_ENGINE=spatie-pdf  # or: mpdf
```

---

## Security Architecture

### 1. Authorization (Laravel Policy)
- **Layer:** Policy
- **Protection:** Unauthorized access
- **Implementation:** `AppointmentPolicy::downloadInvoice()`

### 2. Rate Limiting (Throttle Middleware)
- **Layer:** Middleware
- **Protection:** DoS attacks
- **Implementation:** `throttle:invoice` (10/min)

### 3. UUID Filenames
- **Layer:** Application
- **Protection:** Path traversal
- **Implementation:** `invoice_FV-2025-12-0001_{uuid}.pdf`

### 4. Blade Auto-Escaping
- **Layer:** Template
- **Protection:** XSS in PDF
- **Implementation:** `{{ }}` not `{!! !!}`

### 5. No SSRF (PDF config)
- **Layer:** PDF Engine
- **Protection:** Server-Side Request Forgery
- **Implementation:** Remote files disabled

### 6. Input Validation
- **Layer:** Model
- **Protection:** SQL injection, type errors
- **Implementation:** `$casts = ['price' => 'decimal:2']`

### 7. Memory & Timeout Limits
- **Layer:** Configuration
- **Protection:** Resource exhaustion
- **Implementation:** 256M mem, 60s timeout

---

## Performance Considerations

### PDF Generation Time

| Engine | Average Time | Memory Usage |
|--------|--------------|--------------|
| **Spatie PDF (Puppeteer)** | 2-4s | ~150MB |
| **mPDF** | 0.5-1s | ~50MB |

**Optimization Strategies:**
1. **Eager loading:** `$appointment->load(['service', 'customer'])` (N+1 prevention)
2. **Redis cache:** Settings cached for 1h (no DB query per PDF)
3. **No disk I/O:** PDF streamed directly to browser
4. **Queue (future):** Dla bulk generation można użyć queues

### Concurrency

**Problem:** 100 użytkowników pobiera faktury jednocześnie
**Solution:**
- Rate limiting: Max 10/min per user
- Redis lock: Max 5s wait for invoice number generation
- HTTP keep-alive: Reuse connections

---

## Testing Strategy

### Unit Tests
- `InvoiceNumberGeneratorTest`: Format, sekwencja, konkurencja
- `InvoiceDataTest`: Transformacja danych, VAT calculations
- `AppointmentTest`: Price breakdown method

### Feature Tests
- `InvoiceDownloadTest`:
  - ✅ Customer can download own invoice
  - ❌ Customer cannot download other invoice
  - ✅ Admin can download any invoice
  - ✅ Staff can download assigned invoice
  - ❌ Guest redirected to login
  - ✅ Rate limiting (429 after 10 requests)
  - ✅ PDF has correct Content-Type header

### Integration Tests (Manual)
- Puppeteer installation
- mPDF fallback
- Polish characters rendering
- Multi-page invoices (future)

---

## Deployment Checklist

- [ ] Install Spatie Laravel-PDF (`composer require spatie/laravel-pdf`)
- [ ] Install Puppeteer (`npm install --save-dev puppeteer`)
- [ ] Run migrations (`php artisan migrate`)
- [ ] Seed invoice settings (`php artisan db:seed --class=SettingSeeder`)
- [ ] Configure company data in `/admin/system-settings → Faktury`
- [ ] Test PDF generation on staging
- [ ] Verify Polish characters render correctly
- [ ] Test rate limiting (10/min)
- [ ] Test authorization matrix (customer/staff/admin)
- [ ] Clear caches (`php artisan optimize:clear`)
- [ ] Deploy to production
- [ ] Monitor Laravel Horizon for errors

---

## Future Enhancements

### v1.1: Advanced Features
- [ ] Email invoice as PDF attachment
- [ ] Bulk invoice generation (admin panel)
- [ ] Invoice corrections (Faktura korygująca)
- [ ] Multi-page invoices (multiple services)

### v1.2: Integration
- [ ] KSeF integration (Polski system e-Faktur)
- [ ] Accounting software export (CSV/XML)
- [ ] Invoice archiving (S3 storage)

### v1.3: Customization
- [ ] Custom invoice templates (per company)
- [ ] Logo upload in Settings
- [ ] Color scheme customization

---

**Ostatnia aktualizacja:** 19 grudnia 2024
**Wersja architektury:** 1.0
**Status:** Zaplanowane - oczekuje na budżet
