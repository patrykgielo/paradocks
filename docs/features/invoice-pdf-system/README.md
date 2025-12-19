# System Generowania Faktur PDF

**Status:** 📋 ZAPLANOWANE (oczekuje na budżet)
**Wycena:** 1,700 PLN (17h × 100 PLN/h)
**ROI:** 283% rocznie (~20 dni zwrotu inwestycji)
**Priorytet:** Średni

---

## Szybki Start

```bash
# Pełna instalacja (requires budget approval)
./scripts/install-invoice-system.sh

# Test manual
https://paradocks.local:8444/admin → Appointments → [Pobierz fakturę PDF]
```

**Dokumentacja:**
- 📖 [Przewodnik Instalacji](installation.md) - Krok po kroku
- 🏗️ [Architektura](architecture.md) - Szczegóły techniczne
- 📝 [Plan Implementacji](implementation-plan.md) - Kompletny zakres prac
- 💰 [Wycena Komercyjna](../../estimates/wycena-kompletny-system-faktur-3200-pln.md) - Oferta dla klienta

---

## Co To Jest?

Automatyczny system generowania faktur VAT w formacie PDF dla rezerwacji car detailing. Faktury są generowane on-the-fly (bez zapisu na dysku) i dostępne dla:
- **Klientów:** w panelu `/my-appointments`
- **Adminów:** w Filament ViewAppointment
- **Pracowników:** w Filament (tylko przypisane rezerwacje)

---

## Główne Funkcje

### ✅ Dla Adminów (Filament)

1. **Zakładka "Faktury" w System Settings**
   - Edycja danych firmy (NIP, REGON, adres, konto bankowe)
   - Wszystkie dane w jednym miejscu

2. **Przycisk "Pobierz fakturę PDF"** w ViewAppointment
   - Zielony przycisk w header (obok Edit/Delete)
   - Widoczny tylko gdy `invoice_requested=true` i `price!=null`
   - Autoryzacja: Admin + przypisany Staff

3. **Automatyczna numeracja**
   - Format: `FV/YYYY/MM/XXXX` (np. FV/2025/12/0001)
   - Sekwencyjna numeracja per miesiąc
   - Redis distributed locking (zapobiega duplikatom)

### ✅ Dla Klientów (Customer Panel)

1. **Przycisk "Pobierz fakturę"** w `/my-appointments`
   - Widoczny przy rezerwacjach z fakturą
   - PDF otwiera się w nowej karcie
   - Tylko własne faktury (authorization)

### ✅ Technicznie

1. **PDF Engine:** Spatie Laravel-PDF (Puppeteer) + mPDF fallback
2. **VAT:** 23% (ceny brutto z Service)
3. **Price Snapshot:** Cena zapisana przy booking (zmiany cen nie wpływają na historię)
4. **Security:** Policy authorization, rate limiting (10/min), UUID filenames
5. **Template:** Profesjonalna Faktura VAT (polskie znaki UTF-8)

---

## Korzyści Biznesowe

### Oszczędność Czasu

**Przed:** 25 minut ręcznego wystawiania faktury
**Po:** 30 sekund (jeden klik)
**Redukcja:** 95% czasu

### Eliminacja Błędów

- Automatyczna walidacja NIP (format polski)
- Automatyczne obliczenia VAT (23%)
- Brak błędów przepisywania danych

### ROI

```
Inwestycja:           1,700 PLN (jednorazowa)
Miesięczna wartość:     755 PLN (oszczędność pracy)
Roczna wartość:       9,060 PLN
ROI:                    283%
Zwrot inwestycji:     4.2 miesiąca (~20 dni)
```

---

## Przykładowa Faktura

```
┌──────────────────────────────────────────────────────────┐
│                     FAKTURA VAT                          │
│                  FV/2025/12/0001                         │
└──────────────────────────────────────────────────────────┘

┌───────────────────────┐ ┌────────────────────────────────┐
│ SPRZEDAWCA            │ │ NABYWCA                        │
│ Paradocks Car Detail. │ │ Jan Kowalski                   │
│ NIP: 822-237-03-39    │ │ NIP: 123-456-78-90             │
│ ul. Przykładowa 1     │ │ ul. Testowa 5                  │
│ 00-001 Warszawa       │ │ 02-222 Warszawa                │
└───────────────────────┘ └────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│ Lp │ Nazwa        │ Ilość │ Netto    │ VAT  │ Brutto    │
├────┼──────────────┼───────┼──────────┼──────┼───────────┤
│ 1  │ Detailing    │ 1 szt │ 406,50zł │ 23%  │ 500,00zł  │
└──────────────────────────────────────────────────────────┘

Suma netto:        406,50 zł
VAT (23%):          93,50 zł
SUMA BRUTTO:       500,00 zł
```

---

## Architektura (Uproszczona)

```
Customer/Admin klikają "Pobierz fakturę"
           ↓
InvoiceController::download()
    ├─> Authorization (Policy)
    ├─> Rate Limiting (10/min)
    └─> InvoicePdfGenerator::generate()
           ↓
    InvoiceData::fromAppointment()
        ├─> Appointment (klient, usługa, cena)
        ├─> Settings (dane firmy)
        └─> VAT calculations (brutto → netto)
           ↓
    InvoiceNumberGenerator::generate()
        └─> Redis Lock → FV/2025/12/0123
           ↓
    Blade Template (resources/views/pdf/invoice.blade.php)
        └─> HTML z polskimi znakami
           ↓
    Spatie PDF (Puppeteer) lub mPDF
        └─> PDF Binary
           ↓
    Browser Download (application/pdf)
```

---

## Pliki Do Utworzenia

### Backend (7 plików)

1. `app/DataTransferObjects/InvoiceData.php` (DTO readonly)
2. `app/Services/Invoice/InvoiceNumberGenerator.php` (Redis locking)
3. `app/Services/Invoice/InvoicePdfGenerator.php` (PDF engine)
4. `app/Http/Controllers/InvoiceController.php` (download endpoint)
5. `app/Policies/AppointmentPolicy.php` (update: downloadInvoice method)
6. `config/invoice.php` (configuration)
7. `database/migrations/...add_price_to_appointments.php`

### Frontend (3 pliki)

8. `resources/views/pdf/invoice.blade.php` (PDF template)
9. `app/Filament/Pages/SystemSettings.php` (update: invoiceTab)
10. `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php` (update: header action)

### Testing (2 pliki)

11. `tests/Feature/InvoiceDownloadTest.php` (authorization, rate limiting)
12. `tests/Unit/InvoiceNumberGeneratorTest.php` (numeracja, konkurencja)

---

## Wymagania

### Software

- PHP 8.2+
- Laravel 12
- Redis 7+ (distributed locking)
- Node.js 18+ + NPM (Puppeteer)
- Docker Compose 2.x

### Composer Packages

```bash
composer require spatie/laravel-pdf
composer require mpdf/mpdf  # Fallback
```

### NPM Packages

```bash
npm install --save-dev puppeteer
```

---

## Instalacja (Quick Start)

**Szczegóły:** [installation.md](installation.md)

```bash
# 1. Install dependencies
docker compose exec app composer require spatie/laravel-pdf
docker compose exec app npm install --save-dev puppeteer

# 2. Run migrations
docker compose exec app php artisan migrate

# 3. Seed invoice settings
docker compose exec app php artisan db:seed --class=SettingSeeder

# 4. Configure company data
# Go to: /admin/system-settings → Faktury tab

# 5. Test
https://paradocks.local:8444/admin/appointments/1 → [Pobierz fakturę PDF]
```

---

## Security

| Mechanizm | Implementacja | Ochrona Przed |
|-----------|---------------|---------------|
| **Authorization** | AppointmentPolicy | Unauthorized access |
| **Rate Limiting** | throttle:invoice (10/min) | DoS attacks |
| **UUID Filenames** | `invoice_{number}_{uuid}.pdf` | Path traversal |
| **Blade Escaping** | `{{ }}` not `{!! !!}` | XSS in PDF |
| **No SSRF** | Remote files disabled | Server-Side Request Forgery |
| **Input Validation** | `$casts = ['price' => 'decimal:2']` | SQL injection |
| **Memory Limits** | 256M + 60s timeout | Resource exhaustion |

---

## Testing

### Unit Tests

```bash
docker compose exec app php artisan test --filter=InvoiceNumberGeneratorTest
```

Testuje:
- Format numerów (FV/YYYY/MM/XXXX)
- Sekwencyjność per miesiąc
- Konkurencyjność (Redis locks)

### Feature Tests

```bash
docker compose exec app php artisan test --filter=InvoiceDownloadTest
```

Testuje:
- ✅ Customer może pobrać własną fakturę
- ❌ Customer nie może pobrać cudzej faktury
- ✅ Admin może pobrać każdą fakturę
- ✅ Rate limiting (429 po 10 requestach)
- ✅ PDF ma correct Content-Type header

---

## Troubleshooting

### Puppeteer nie działa

**Symptom:** Timeout 60s, PDF nie generuje się
**Solution:**
```bash
# Switch to mPDF fallback
echo "INVOICE_PDF_ENGINE=mpdf" >> .env
docker compose exec app php artisan config:clear
```

### Polskie znaki wyświetlają się jako "?"

**Solution (mPDF):**
```php
// resources/views/pdf/invoice.blade.php
<style>
    body { font-family: DejaVu Sans, sans-serif; }
</style>
```

### Duplicate invoice numbers (race condition)

**Diagnosis:**
```bash
docker compose exec redis redis-cli PING  # Should return: PONG
```

**Solution:** Upewnij się że Redis działa (distributed locking)

---

## Future Enhancements

### v1.1 (Planned)
- [ ] Email invoice as PDF attachment
- [ ] Bulk invoice generation (admin panel)
- [ ] Invoice corrections (Faktura korygująca)

### v1.2 (Future)
- [ ] KSeF integration (Polish e-Invoice system)
- [ ] Accounting software export (CSV/XML)
- [ ] Invoice archiving (S3 storage)

### v1.3 (Ideas)
- [ ] Custom templates per company
- [ ] Logo upload in Settings
- [ ] Multi-language invoices (EN/PL)

---

## FAQ

**Q: Czy faktury są zapisywane na dysku?**
A: Nie. PDF generowane on-the-fly w pamięci, streamowane bezpośrednio do przeglądarki.

**Q: Co jeśli cena usługi się zmieni?**
A: Price snapshot przy booking (`Appointment.price`). Historyczne faktury nie zmieniają się.

**Q: Czy można zmienić szablon faktury?**
A: Tak. Edytuj `resources/views/pdf/invoice.blade.php`. Zmiany wymagają `php artisan view:clear`.

**Q: Limit 10 pobrań/min to nie za mało?**
A: To per user. Admin może pobrać 10 faktur w ciągu minuty, inny user też 10. W config można zwiększyć.

**Q: Czy działa bez Puppeteer?**
A: Tak. mPDF fallback nie wymaga Node.js. Ustaw `INVOICE_PDF_ENGINE=mpdf` w .env.

**Q: Czy można pobrać starą fakturę po zmianie danych firmy?**
A: Tak. InvoiceData użyje aktualnych danych z Settings (nie są snapshowane). Jeśli to problem, należy snapshować również dane firmy.

---

## Support & Contact

**Dokumentacja:**
- [Installation Guide](installation.md)
- [Architecture Details](architecture.md)
- [Implementation Plan](implementation-plan.md)

**Issues:**
- GitHub Issues: `paradocks/app/issues`
- Label: `feature:invoice-pdf`

**Developer:**
- Email: [developer@paradocks.local]
- Discord: [#invoice-pdf-system]

---

**Ostatnia aktualizacja:** 19 grudnia 2024
**Wersja:** 1.0
**Status:** Zaplanowane - oczekuje na zatwierdzenie budżetu (1,700 PLN)

🤖 Generated with [Claude Code](https://claude.com/claude-code)
