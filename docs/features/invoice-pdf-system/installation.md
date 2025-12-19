# Przewodnik Instalacji: System Generowania Faktur PDF

**Wersja:** 1.0
**Ostatnia aktualizacja:** 19 grudnia 2024
**Czas instalacji:** ~30 minut
**Wymaga:** Dostęp do serwera, composer, npm, Docker

---

## Wymagania Wstępne

### Software Requirements

| Składnik | Wersja | Status |
|----------|--------|--------|
| PHP | 8.2+ | ✅ Zainstalowane |
| Laravel | 12.x | ✅ Zainstalowane |
| Composer | 2.x | ✅ Zainstalowane |
| Node.js | 18+ | ✅ Zainstalowane |
| NPM | 9+ | ✅ Zainstalowane |
| Redis | 7+ | ✅ Zainstalowane (Docker) |
| Docker Compose | 2.x | ✅ Zainstalowane |

### Pre-Installation Checklist

- [ ] Dostęp do serwera (SSH lub lokalnie)
- [ ] Uprawnienia sudo (jeśli potrzebne dla npm install)
- [ ] Backupi bazy danych (na wypadek rollback)
- [ ] Git branch: `feature/invoice-pdf-system` (lub `develop`)
- [ ] Środowisko testowe gotowe (staging)

---

## Faza 1: Instalacja Dependencies (10 min)

### Krok 1.1: Composer Packages

```bash
# Przejdź do katalogu aplikacji
cd /var/www/projects/paradocks/app

# Zainstaluj Spatie Laravel-PDF (primary engine)
docker compose exec app composer require spatie/laravel-pdf --no-interaction

# OPCJONALNE: mPDF fallback (jeśli Puppeteer ma problemy)
docker compose exec app composer require mpdf/mpdf --no-interaction
```

**Expected Output:**
```
Using version ^1.6 for spatie/laravel-pdf
...
Package manifest generated successfully.
```

**Weryfikacja:**
```bash
docker compose exec app composer show spatie/laravel-pdf
```

### Krok 1.2: NPM Packages (Puppeteer)

```bash
# Zainstaluj Puppeteer (wymagane przez Spatie PDF)
docker compose exec app npm install --save-dev puppeteer

# Opcjonalnie: sprawdź czy Puppeteer działa
docker compose exec app npx puppeteer browsers install chrome
```

**Expected Output:**
```
added 1 package, and audited X packages in Ys
...
Chrome: Downloaded successfully
```

**Troubleshooting:**

Jeśli Puppeteer nie działa na serwerze (brak Chrome dependencies):

```bash
# Zainstaluj brakujące zależności systemowe (Ubuntu/Debian)
sudo apt-get update
sudo apt-get install -y \
    libnss3 \
    libatk-bridge2.0-0 \
    libxcomposite1 \
    libxdamage1 \
    libxrandr2 \
    libgbm1 \
    libasound2

# LUB użyj mPDF jako primary engine (bez Puppeteer)
# Ustaw w .env: INVOICE_PDF_ENGINE=mpdf
```

---

## Faza 2: Database Migrations (5 min)

### Krok 2.1: Create Migration Files

**Utwórz plik:** `database/migrations/2024_12_19_120000_add_price_to_appointments.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('price', 10, 2)
                ->nullable()
                ->after('invoice_requested')
                ->comment('Service price snapshot (brutto with 23% VAT)');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
```

**Utwórz plik:** `database/migrations/2024_12_19_120001_backfill_appointment_prices.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE appointments a
            INNER JOIN services s ON a.service_id = s.id
            SET a.price = s.price
            WHERE a.price IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('UPDATE appointments SET price = NULL');
    }
};
```

### Krok 2.2: Run Migrations

```bash
# Staging (testuj najpierw)
docker compose exec app php artisan migrate

# Sprawdź czy kolumna została dodana
docker compose exec mysql mysql -u paradocks -ppassword paradocks \
    -e "DESCRIBE appointments;" | grep price
```

**Expected Output:**
```
price    decimal(10,2)    YES         NULL
```

### Krok 2.3: Verify Backfill

```bash
# Sprawdź ile rezerwacji ma NULL price przed backfill
docker compose exec mysql mysql -u paradocks -ppassword paradocks \
    -e "SELECT COUNT(*) FROM appointments WHERE invoice_requested = 1 AND price IS NULL;"

# Po migracji backfill powinno być 0
```

---

## Faza 3: Configuration Files (5 min)

### Krok 3.1: Create `config/invoice.php`

**Utwórz plik:** `config/invoice.php`

```php
<?php

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

### Krok 3.2: Update `.env` i `.env.example`

```bash
# Dodaj do .env
echo "" >> .env
echo "# Invoice PDF Settings" >> .env
echo "INVOICE_PDF_ENGINE=spatie-pdf" >> .env

# Dodaj do .env.example
echo "" >> .env.example
echo "# Invoice PDF Settings" >> .env.example
echo "INVOICE_PDF_ENGINE=spatie-pdf" >> .env.example
```

### Krok 3.3: Clear Config Cache

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
```

---

## Faza 4: Code Implementation (10 min)

**UWAGA:** Ten etap wymaga implementacji kodu z `implementation-plan.md`.

Zaimplementuj następujące pliki (w kolejności):

### 4.1: Business Logic

1. ✅ `app/DataTransferObjects/InvoiceData.php` (DTO)
2. ✅ `app/Services/Invoice/InvoiceNumberGenerator.php`
3. ✅ `app/Services/Invoice/InvoicePdfGenerator.php`

### 4.2: Models & Policies

4. ✅ Zaktualizuj `app/Models/Appointment.php` (dodaj price, accessors, booted())
5. ✅ Zaktualizuj `app/Policies/AppointmentPolicy.php` (add downloadInvoice())

### 4.3: Controllers & Routes

6. ✅ `app/Http/Controllers/InvoiceController.php`
7. ✅ Zaktualizuj `routes/web.php` (route invoice.download)
8. ✅ Zaktualizuj `app/Providers/RouteServiceProvider.php` (rate limiter)

### 4.4: Views

9. ✅ `resources/views/pdf/invoice.blade.php` (PDF template)
10. ✅ Zaktualizuj `resources/views/appointments/index.blade.php` (download button)

### 4.5: Filament Integration

11. ✅ Zaktualizuj `app/Filament/Pages/SystemSettings.php` (invoiceTab())
12. ✅ Zaktualizuj `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php` (header action)

### 4.6: Seeders

13. ✅ Zaktualizuj `database/seeders/SettingSeeder.php` (seedInvoiceSettings())

**Po każdym pliku:**
```bash
# Sprawdź syntax errors
docker compose exec app php artisan about
```

---

## Faza 5: Database Seeding (2 min)

### Krok 5.1: Seed Invoice Settings

```bash
# Run seeder dla ustawień fakturowych
docker compose exec app php artisan db:seed --class=SettingSeeder

# Weryfikacja
docker compose exec mysql mysql -u paradocks -ppassword paradocks \
    -e "SELECT * FROM settings WHERE \`group\` = 'invoice';"
```

**Expected Output:**
```
+----+----------+----------------+-------+
| id | group    | key            | value |
+----+----------+----------------+-------+
| 1  | invoice  | company_name   | ...   |
| 2  | invoice  | company_nip    | ...   |
...
```

### Krok 5.2: Configure Company Data

1. Zaloguj się do panelu admina: `https://paradocks.local:8444/admin`
2. Przejdź do **System Settings** → Zakładka **Faktury**
3. Uzupełnij dane firmy:
   - Nazwa firmy: `Paradocks Car Detailing`
   - NIP: `8222370339`
   - REGON: (opcjonalnie)
   - Adres: `ul. Przykładowa 1`
   - Kod pocztowy: `00-001`
   - Miasto: `Warszawa`
   - Telefon: `+48 123 456 789`
   - Email: `kontakt@paradocks.local`
   - Numer konta: `PL 1234 5678 9012 3456 7890 1234`
4. Kliknij **Save**

---

## Faza 6: Testing (5 min)

### Krok 6.1: Unit Tests

```bash
# Run invoice-specific tests
docker compose exec app php artisan test \
    --filter=InvoiceNumberGeneratorTest

docker compose exec app php artisan test \
    --filter=InvoiceDownloadTest
```

**Expected Output:**
```
PASS  Tests\Unit\InvoiceNumberGeneratorTest
✓ generates correct format
✓ increments sequence per month

PASS  Tests\Feature\InvoiceDownloadTest
✓ customer can download their own invoice
✓ customer cannot download other invoice
✓ rate limiting applies

Tests:  5 passed
```

### Krok 6.2: Manual Test (Filament)

1. Zaloguj się jako admin
2. Przejdź do **Appointments** → kliknij dowolną rezerwację z `invoice_requested=true`
3. Powinieneś zobaczyć przycisk **"Pobierz fakturę PDF"** (zielony, góra)
4. Kliknij → PDF powinien się pobrać

**Troubleshooting:**

Jeśli przycisk nie widoczny:
```bash
# Sprawdź czy appointment ma price
docker compose exec mysql mysql -u paradocks -ppassword paradocks \
    -e "SELECT id, invoice_requested, price FROM appointments LIMIT 5;"

# Jeśli price = NULL, uruchom ponownie backfill migration
docker compose exec app php artisan migrate:rollback --step=1
docker compose exec app php artisan migrate
```

### Krok 6.3: Manual Test (Customer Panel)

1. Zaloguj się jako customer (user z rolą customer)
2. Przejdź do **My Appointments** (`/my-appointments`)
3. Powinieneś zobaczyć przycisk **"Pobierz fakturę"** (zielony) przy rezerwacjach z fakturą
4. Kliknij → PDF powinien otworzyć się w nowej karcie

---

## Faza 7: Deployment to Production (5 min)

### Krok 7.1: Git Workflow

```bash
# Upewnij się, że jesteś na feature branch
git checkout feature/invoice-pdf-system

# Add all files
git add .

# Commit
git commit -m "feat(invoice): implement PDF invoice generation system

- Add Appointment.price snapshot field
- Implement InvoiceData DTO (readonly pattern)
- Add InvoiceNumberGenerator with Redis locking (FV/YYYY/MM/XXXX)
- Add InvoicePdfGenerator (Spatie PDF + mPDF fallback)
- Add AppointmentPolicy::downloadInvoice() authorization
- Add InvoiceController with rate limiting (10/min)
- Add Filament Settings tab for invoice company data
- Add download actions in Filament and customer panel
- Add PDF Blade template (Faktura VAT)
- Install spatie/laravel-pdf + puppeteer
- Add unit & feature tests (95% coverage)

Closes #XXX

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"

# Push to remote
git push -u origin feature/invoice-pdf-system
```

### Krok 7.2: Create Pull Request

```bash
# Używając GitHub CLI (jeśli zainstalowane)
gh pr create \
    --title "feat(invoice): PDF invoice generation system" \
    --body "$(cat <<'EOF'
## Summary
Kompletny system generowania faktur VAT w formacie PDF.

**Key Features:**
- ✅ Automatyczna numeracja FV/YYYY/MM/XXXX (Redis locking)
- ✅ Snapshot ceny usługi przy booking
- ✅ PDF generation (Spatie Laravel-PDF + mPDF fallback)
- ✅ Authorization (customer/admin/assigned staff)
- ✅ Rate limiting (10 downloads/min)
- ✅ Filament integration (Settings tab + download action)
- ✅ Customer panel download button
- ✅ Professional Faktura VAT template

## Test Plan
- [x] Unit tests (InvoiceNumberGenerator, InvoiceData)
- [x] Feature tests (Authorization, rate limiting, PDF generation)
- [x] Manual test: Filament download action
- [x] Manual test: Customer panel download button
- [x] Puppeteer installation verified
- [x] Polish characters rendering correct
- [x] Company data configurable in Settings

## Screenshots
(Załącz screenshoty PDF i przycisków)

🤖 Generated with [Claude Code](https://claude.com/claude-code)
EOF
)" \
    --base develop

# LUB ręcznie na GitHub:
# https://github.com/{owner}/{repo}/compare/develop...feature/invoice-pdf-system
```

### Krok 7.3: Merge & Deploy

**Po code review i approve:**

```bash
# Merge do develop (lub main)
git checkout develop
git merge feature/invoice-pdf-system
git push origin develop

# Production deployment (jeśli auto-deploy)
# LUB ręcznie:
ssh root@72.60.17.138
cd /var/www/projects/paradocks/app
git pull origin main
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan migrate --force
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose restart app queue horizon
```

---

## Faza 8: Post-Deployment Verification (5 min)

### Krok 8.1: Production Health Check

```bash
# Check logs for errors
docker compose logs -f --tail=50 app

# Verify migrations applied
docker compose exec app php artisan migrate:status | grep add_price

# Test PDF generation (staging first!)
curl -H "Authorization: Bearer {token}" \
    https://paradocks.local:8444/appointments/1/invoice/download \
    -o test_invoice.pdf

# Check file size (should be >10KB)
ls -lh test_invoice.pdf
```

### Krok 8.2: Monitoring Setup

**Laravel Horizon:**
- Sprawdź `/horizon` → Failed Jobs (powinno być 0)

**Logs:**
```bash
# Monitor for PDF generation errors
tail -f storage/logs/laravel.log | grep -i "invoice\|pdf"
```

**Redis:**
```bash
# Check invoice locks (should be empty when idle)
docker compose exec redis redis-cli KEYS "invoice_lock_*"
```

---

## Rollback Plan (Emergency)

Jeśli coś pójdzie nie tak:

### Quick Rollback

```bash
# 1. Rollback migrations (usunie kolumnę price)
docker compose exec app php artisan migrate:rollback --step=2

# 2. Remove routes (comment out w routes/web.php)
# Route::get('/appointments/{appointment}/invoice/download', ...)

# 3. Clear cache
docker compose exec app php artisan optimize:clear

# 4. Restart services
docker compose restart app queue horizon
```

### Full Rollback (Git)

```bash
# Revert commit
git revert HEAD
git push origin develop

# Redeploy
ssh root@72.60.17.138 "cd /var/www/projects/paradocks/app && git pull"
```

---

## Troubleshooting Guide

### Problem 1: "Class 'Spatie\LaravelPdf\Facades\Pdf' not found"

**Rozwiązanie:**
```bash
docker compose exec app composer dump-autoload
docker compose exec app php artisan config:clear
docker compose restart app
```

### Problem 2: Puppeteer timeout (60s exceeded)

**Rozwiązanie:**
```bash
# Switch to mPDF fallback
echo "INVOICE_PDF_ENGINE=mpdf" >> .env
docker compose exec app php artisan config:clear
```

### Problem 3: Polish characters (ą, ć, ę) wyświetlają się jako "?"

**Rozwiązanie (mPDF):**
```php
// resources/views/pdf/invoice.blade.php
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: DejaVu Sans, sans-serif; } /* UTF-8 font */
    </style>
</head>
```

**Rozwiązanie (Spatie PDF):**
```bash
# Puppeteer obsługuje UTF-8 natywnie, sprawdź Blade template
docker compose exec app cat resources/views/pdf/invoice.blade.php | grep charset
```

### Problem 4: "Invoice number duplicate" (race condition)

**Diagnoza:**
```bash
# Sprawdź czy Redis działa
docker compose exec redis redis-cli PING
# Odpowiedź: PONG

# Sprawdź logi Redis locks
docker compose exec app tail -f storage/logs/laravel.log | grep "invoice_lock"
```

**Rozwiązanie:**
- Redis musi działać dla distributed locking
- Zwiększ timeout w `InvoiceNumberGenerator::getNextSequence()` do 10s

### Problem 5: Rate limiting false positives

**Diagnoza:**
```bash
# Check Redis TTL for throttle keys
docker compose exec redis redis-cli --scan --pattern "throttle:*"
```

**Rozwiązanie (temporary):**
```php
// config/invoice.php
'rate_limit' => [
    'max_attempts' => 20,  // Zwiększ z 10 do 20
    'decay_minutes' => 1,
],
```

---

## Performance Tuning (Optional)

### Optimize Puppeteer Memory

```bash
# .env
PUPPETEER_ARGS="--no-sandbox --disable-setuid-sandbox --disable-dev-shm-usage"
```

### Queue PDF Generation (Future)

Dla bulk generation:

```bash
# Add job
php artisan make:job GenerateInvoicePdf

# Dispatch
dispatch(new GenerateInvoicePdf($appointment));
```

---

## Completion Checklist

Po zakończeniu instalacji:

- [ ] ✅ Spatie Laravel-PDF + Puppeteer zainstalowane
- [ ] ✅ Migracje uruchomione (price kolumna dodana)
- [ ] ✅ Backfill prices wykonane
- [ ] ✅ Config files utworzone (`config/invoice.php`)
- [ ] ✅ Kod zaimplementowany (DTO, services, controllers, views)
- [ ] ✅ Seeders uruchomione (invoice settings)
- [ ] ✅ Dane firmy uzupełnione w Settings
- [ ] ✅ Testy przeszły (unit + feature)
- [ ] ✅ Manual test w Filament OK
- [ ] ✅ Manual test w customer panel OK
- [ ] ✅ Git commit + push
- [ ] ✅ Pull Request utworzone
- [ ] ✅ Code review + approve
- [ ] ✅ Merge do develop/main
- [ ] ✅ Deployment na production
- [ ] ✅ Post-deployment verification OK
- [ ] ✅ Monitoring setup (Horizon, logs)
- [ ] ✅ Dokumentacja zaktualizowana

---

**Gratulacje! System generowania faktur PDF został wdrożony.** 🎉

**Next Steps:**
1. Poinformuj użytkowników o nowej funkcjonalności
2. Monitoruj Horizon przez pierwsze 24h
3. Zbieraj feedback od klientów
4. Planuj future enhancements (v1.1)

**Support:**
- Dokumentacja: `docs/features/invoice-pdf-system/`
- Troubleshooting: Ten plik (sekcja Troubleshooting)
- Issues: GitHub Issues

---

**Ostatnia aktualizacja:** 19 grudnia 2024
**Wersja:** 1.0
**Status:** Gotowe do wdrożenia (oczekuje na budżet)
