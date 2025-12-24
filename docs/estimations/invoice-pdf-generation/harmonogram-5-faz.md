# Harmonogram Implementacji: 5 Faz (10 dni)

**Projekt:** System Generowania Faktur PDF
**Total Effort:** 30h
**Timeline:** 10 dni roboczych (3h/dzień avg)
**Branch:** feature/invoice-pdf-generation

---

## Przegląd Faz

| Faza | Scope | Godziny | Dni | Deliverables |
|------|-------|---------|-----|--------------|
| **1. Foundation** | Settings + Models | 6.5h | 1-2 | Settings edytowalne, Invoice models ready |
| **2. PDF Engine** | Number Generator + PDF | 8h | 3-4 | PDF generation working, przykładowa faktura |
| **3. Filament** | Admin Integration | 7h | 5-6 | CRUD faktur, action w ViewAppointment |
| **4. Automation** | Email + Queue | 2h | 7 | Email z PDF działający |
| **5. QA** | Tests + Docs | 7h | 8-10 | 95% coverage, production ready |

---

## Faza 1: Foundation (Dni 1-2, 6.5h)

### Cel
Przygotować fundament systemu: możliwość edycji danych firmy w panelu admina i utworzenie modeli Invoice/InvoiceItem w bazie danych.

### Scope Prac

#### A. Settings System (2.5h)

**Tasks:**
1. Rozszerzyć `app/Filament/Pages/SystemSettings.php`:
   - Nowa metoda `invoiceTab()` z FormSchema
   - Pola: company_name, nip, regon, address (4 pola), bank_account
   - Logo upload (FileUpload component)
2. Walidacja:
   - Reuse `ValidNIP` rule dla NIP firmy
   - Required validation (wszystkie pola oprócz REGON)
3. Seeders:
   - `InvoiceSettingSeeder` z placeholder danymi

**Files Created:**
- `database/seeders/InvoiceSettingSeeder.php`

**Files Modified:**
- `app/Filament/Pages/SystemSettings.php`

**Testing:**
- Manual: Otworzyć `/admin/system-settings` → zakładka "Dane firmy" → edytować pola → save

**Deliverable:**
✅ Admin może edytować dane firmy w panelu

#### B. Invoice Models (4h)

**Tasks:**
1. Utworzyć `app/Models/Invoice.php`:
   - Fields: number, issue_date, sale_date, booking_id, total_net, total_vat, total_gross
   - Casts: dates, decimals
   - Relation: belongsTo(Appointment)
2. Utworzyć `app/Models/InvoiceItem.php`:
   - Fields: invoice_id, name, quantity, unit_price_net, vat_rate, total_net, total_vat, total_gross
   - Relation: belongsTo(Invoice)
3. Migracje:
   - `create_invoices_table.php`
   - `create_invoice_items_table.php`
   - Indexes: booking_id, issue_date
4. Factories:
   - `InvoiceFactory.php`
   - `InvoiceItemFactory.php`
5. Update `Appointment` model:
   - Relacja: hasOne(Invoice)

**Files Created:**
- `app/Models/Invoice.php`
- `app/Models/InvoiceItem.php`
- `database/migrations/YYYY_MM_DD_create_invoices_table.php`
- `database/migrations/YYYY_MM_DD_create_invoice_items_table.php`
- `database/factories/InvoiceFactory.php`
- `database/factories/InvoiceItemFactory.php`

**Files Modified:**
- `app/Models/Appointment.php`

**Testing:**
- Unit: Factory generation test (Invoice::factory()->create())
- Feature: Relationship test (appointment->invoice)

**Deliverable:**
✅ Tabele invoices + invoice_items w bazie danych
✅ Modele z relacjami działające

---

### Checkpoint #1 (Po Dniu 2)

**Demo dla klienta:**
- Settings "Dane firmy" działające (pokazać edycję)
- Invoice models ready (pokazać w Tinker: Invoice::factory()->create())

**Kryteria akceptacji:**
- [ ] Admin może zapisać dane firmy w Settings
- [ ] Logo uploaduje się i wyświetla preview
- [ ] Invoice factory tworzy poprawne rekordy w DB
- [ ] Migracje przechodzą bez błędów

---

## Faza 2: PDF Engine (Dni 3-4, 8h)

### Cel
Zaimplementować core logikę generowania PDF: numerację sekwencyjną i rendering Blade → PDF.

### Scope Prac

#### C. InvoiceNumberGenerator (3h)

**Tasks:**
1. Utworzyć `app/Services/Invoice/InvoiceNumberGenerator.php`:
   - Metoda `generate(Appointment $appointment): string`
   - Format: FV/YYYY/MM/XXXX (z padding 4 cyfry)
   - Redis locking: `Cache::lock("invoice_lock_{year}_{month}", 10)`
   - Query DB: count invoices in current month → sequence + 1
2. Config:
   - `config/invoice.php` z formatem numeracji
3. Update `Invoice` model:
   - Auto-generate number w `creating` event

**Files Created:**
- `app/Services/Invoice/InvoiceNumberGenerator.php`
- `config/invoice.php`

**Files Modified:**
- `app/Models/Invoice.php`

**Testing:**
- Unit: Format test (FV/2025/12/0001)
- Unit: Sequence test (0001, 0002, 0003)
- Unit: Month reset test (grudzień 0099 → styczeń 0001)
- Integration: Concurrent generation (2 processes) → no duplicates

**Deliverable:**
✅ Numeracja sekwencyjna działająca
✅ Redis locking zapobiega duplikatom

#### D. PDF Generator + Template (5h)

**Tasks:**
1. Install barryvdh/laravel-dompdf:
   ```bash
   composer require barryvdh/laravel-dompdf
   ```
2. Utworzyć `app/Services/Invoice/InvoicePdfGenerator.php`:
   - Metoda `generate(Invoice $invoice): Response`
   - Load Settings (dane firmy)
   - Load Invoice + InvoiceItems (eager loading)
   - Render Blade → PDF
   - Stream PDF response
3. Utworzyć `resources/views/pdf/invoice.blade.php`:
   - **Header:** Logo firmy (z Settings) + dane sprzedawcy
   - **Metadata:** Numer faktury, data wystawienia, data sprzedaży
   - **Nabywca:** Dane z invoice_* fields (Appointment)
   - **Tabela pozycji:** InvoiceItems (nazwa, ilość, cena netto, VAT, brutto)
   - **Podsumowanie:** Suma netto, VAT, DO ZAPŁATY (bold)
   - **Footer:** Numer konta, termin płatności (14 dni od wystawienia)
4. Styling:
   - Tailwind utilities (inline styles dla PDF)
   - DejaVu Sans font (polskie znaki)
   - A4 portrait, margins 15mm
5. VAT calculations:
   - Helper method `calculateVAT(float $brutto, int $rate): array`
   - Returns: ['netto', 'vat', 'brutto']

**Files Created:**
- `app/Services/Invoice/InvoicePdfGenerator.php`
- `resources/views/pdf/invoice.blade.php`

**Files Modified:**
- `config/invoice.php` (VAT rate, PDF settings)

**Testing:**
- Feature: Generate PDF test (returns PDF response)
- Feature: Content test (PDF zawiera NIP firmy, nazwę klienta)
- Manual: Otworzyć PDF → sprawdzić polskie znaki (ąćęłńóśźż)

**Deliverable:**
✅ PDF faktury generowany poprawnie
✅ Polskie znaki wyświetlają się
✅ Layout profesjonalny i zgodny z przepisami

---

### Checkpoint #2 (Po Dniu 4)

**Demo dla klienta:**
- Wygenerować przykładową fakturę (z placeholder danymi lub rzeczywistymi)
- Pokazać PDF: layout, logo, polskie znaki
- Potwierdzić akceptację wyglądu

**Kryteria akceptacji:**
- [ ] PDF generuje się bez błędów
- [ ] Polskie znaki wyświetlają się poprawnie (ą, ę, ć, ...)
- [ ] Logo firmy wyświetla się w header
- [ ] Wszystkie wymagane elementy prawne są na fakturze
- [ ] Obliczenia VAT są poprawne (brutto - netto = VAT)

**IMPORTANT:** Klient musi zaakceptować layout przed przejściem do Fazy 3. Jeśli są uwagi - poprawki w ramach tej fazy (max +2h contingency).

---

## Faza 3: Filament Integration (Dni 5-6, 7h)

### Cel
Zintegrować PDF generation z panelem admina: CRUD faktur + action "Wygeneruj fakturę" w ViewAppointment.

### Scope Prac

#### E. InvoiceResource (3h)

**Tasks:**
1. Utworzyć `app/Filament/Resources/InvoiceResource.php`:
   - ListInvoices: table z kolumnami (number, issue_date, customer, total_gross)
   - Filters: DateRangePicker (issue_date), TextInput (customer search)
   - Actions: View, Delete (soft delete)
2. Utworzyć `app/Filament/Resources/InvoiceResource/Pages/ListInvoices.php`
3. Utworzyć `app/Filament/Resources/InvoiceResource/Pages/ViewInvoice.php`:
   - Infolists z sekcjami:
     - Metadata (number, dates)
     - Seller (dane firmy z Settings)
     - Buyer (dane klienta z invoice)
     - Items (table z pozycjami)
     - Totals (netto, VAT, brutto)
   - Header Actions:
     - "Pobierz PDF" (download)
     - "Wyślij email" (queue job - Faza 4)
     - "Regeneruj PDF" (w razie błędu)
4. Authorization:
   - `app/Policies/InvoicePolicy.php`:
     - viewAny: admin + staff
     - view: admin + staff (assigned to booking)
     - delete: admin only

**Files Created:**
- `app/Filament/Resources/InvoiceResource.php`
- `app/Filament/Resources/InvoiceResource/Pages/ListInvoices.php`
- `app/Filament/Resources/InvoiceResource/Pages/ViewInvoice.php`
- `app/Policies/InvoicePolicy.php`

**Testing:**
- Feature: List invoices test (admin widzi wszystkie, staff tylko assigned)
- Feature: View invoice test (infolist wyświetla poprawne dane)
- Feature: Delete invoice test (soft delete, nie hard delete)

**Deliverable:**
✅ Panel CRUD faktur w `/admin/invoices`
✅ Filtrowanie po dacie i kliencie
✅ Header actions (Download/Email/Regenerate)

#### F. AppointmentResource Integration (2h)

**Tasks:**
1. Update `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php`:
   - Header action "Wygeneruj fakturę":
     - Visible when: invoice_requested=true && price!=null && !invoice_generated
     - On click: Create Invoice record → InvoiceNumberGenerator → InvoicePdfGenerator → redirect to ViewInvoice
     - Success notification: "Faktura FV/2025/12/0001 wygenerowana"
   - Info badge (jeśli faktura już istnieje):
     - "Faktura: FV/2025/12/0001" (link do ViewInvoice)
2. Walidacja:
   - Check invoice_* fields complete (NIP, company_name, address)
   - Error notification jeśli brakuje danych

**Files Modified:**
- `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php`

**Testing:**
- Feature: Generate invoice from appointment (tworzy Invoice record)
- Feature: Cannot generate when invoice_requested=false (error)
- Feature: Redirect to ViewInvoice after generation

**Deliverable:**
✅ Action "Wygeneruj fakturę" w ViewAppointment
✅ Walidacja przed generowaniem
✅ Toast notification po sukcesie

#### G. Controller & Routes (1.5h)

**Tasks:**
1. Utworzyć `app/Http/Controllers/InvoiceController.php`:
   - Metoda `download(Invoice $invoice)`:
     - Authorization: InvoicePolicy::download
     - Load invoice + items (eager loading)
     - Generate PDF (InvoicePdfGenerator)
     - Return PDF response (streaming)
2. Route:
   ```php
   Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
       ->middleware(['auth', 'throttle:invoice'])
       ->name('invoices.download');
   ```
3. Throttle middleware:
   - `config/app.php`: `'invoice' => '10,1'` (10 requests per minute)
4. Update `AppointmentPolicy`:
   - Metoda `downloadInvoice(User $user, Appointment $appointment)`:
     - Customer: tylko własne
     - Admin: wszystkie
     - Staff: tylko assigned

**Files Created:**
- `app/Http/Controllers/InvoiceController.php`

**Files Modified:**
- `routes/web.php`
- `config/app.php` (throttle config)
- `app/Policies/AppointmentPolicy.php`

**Testing:**
- Feature: Customer can download own invoice (200 OK)
- Feature: Customer cannot download other invoice (403 Forbidden)
- Feature: Admin can download any invoice (200 OK)
- Feature: Rate limiting (429 after 10 requests)
- Feature: PDF has correct Content-Type header (application/pdf)

**Deliverable:**
✅ Endpoint `/invoices/{id}/download` działający
✅ Authorization policy working
✅ Rate limiting zapobiega abuse

#### H. Customer Panel Integration (0.5h)

**Tasks:**
1. Update `resources/views/my-appointments/show.blade.php`:
   - Conditional button "Pobierz fakturę":
     ```blade
     @if($appointment->invoice && $appointment->invoice_requested)
         <a href="{{ route('invoices.download', $appointment->invoice) }}"
            target="_blank"
            class="btn-primary">
             Pobierz fakturę PDF
         </a>
     @endif
     ```

**Files Modified:**
- `resources/views/my-appointments/show.blade.php` (lub odpowiednik)

**Testing:**
- Feature: Customer widzi przycisk gdy invoice exists
- Feature: Kliknięcie otwiera PDF w nowej karcie

**Deliverable:**
✅ Przycisk "Pobierz fakturę" w profilu klienta

---

### Checkpoint #3 (Po Dniu 6)

**Demo dla klienta:**
- Pokazać pełny workflow:
  1. Admin otwiera ViewAppointment (z invoice_requested=true)
  2. Klika "Wygeneruj fakturę" → faktura tworzy się
  3. Redirect do ViewInvoice → header actions działają
  4. Klient loguje się → widzi przycisk "Pobierz fakturę" → klika → PDF się otwiera

**Kryteria akceptacji:**
- [ ] Admin może wygenerować fakturę z ViewAppointment
- [ ] Lista faktur w `/admin/invoices` działa
- [ ] ViewInvoice wyświetla wszystkie dane poprawnie
- [ ] Klient może pobrać fakturę z profilu
- [ ] Authorization działa (customer/admin/staff)
- [ ] Rate limiting działa (429 po 10 requestach)

---

## Faza 4: Automation (Dzień 7, 2h)

### Cel
Dodać automatyczne wysyłanie faktur emailem (queue job).

### Scope Prac

#### I. Email Notification (2h)

**Tasks:**
1. Utworzyć `app/Mail/InvoiceGenerated.php`:
   - Subject: "Twoja faktura FV/2025/12/0001 - ParaDocks"
   - Attachment: PDF (generated on-the-fly)
   - Template: `resources/views/emails/invoice-generated.blade.php`
   - PL/EN support (na podstawie user locale)
2. Utworzyć `app/Jobs/SendInvoiceEmailJob.php`:
   - Dispatch to queue: redis
   - Generate PDF
   - Send email with attachment
   - Log to email_sends table (existing feature)
3. Update ViewInvoice header action:
   - "Wyślij email" → dispatch SendInvoiceEmailJob
   - Success notification: "Email wysłany do kolejki"
4. Blade template:
   - `resources/views/emails/invoice-generated.blade.php`:
     - Podziękowanie za rezerwację
     - Link do pobrania faktury (URL do /invoices/{id}/download)
     - Załącznik PDF

**Files Created:**
- `app/Mail/InvoiceGenerated.php`
- `app/Jobs/SendInvoiceEmailJob.php`
- `resources/views/emails/invoice-generated.blade.php`

**Files Modified:**
- `app/Filament/Resources/InvoiceResource/Pages/ViewInvoice.php` (header action)

**Testing:**
- Feature: Email sent with PDF attachment
- Feature: Email logged in email_sends table
- Queue: Job dispatched to Redis queue
- Manual: Sprawdzić Mailpit (http://paradocks.local:8025)

**Deliverable:**
✅ Email z PDF załącznikiem wysyłany
✅ Queue job dla async processing
✅ Header action "Wyślij email" w ViewInvoice

---

### Checkpoint #4 (Po Dniu 7)

**Demo dla klienta:**
- Pokazać kompletny system:
  1. Wygenerować fakturę
  2. Kliknąć "Wyślij email"
  3. Sprawdzić Mailpit → email przyszedł z załącznikiem PDF

**Kryteria akceptacji:**
- [ ] Email wysyła się poprawnie
- [ ] PDF attachment jest poprawny (można otworzyć)
- [ ] Email template wygląda profesjonalnie (PL/EN)
- [ ] Queue job nie blokuje UI (async)

**IMPORTANT:** To finalny checkpoint przed testami. Klient musi zaakceptować cały system jako kompletny i działający.

---

## Faza 5: QA & Deployment (Dni 8-10, 7h)

### Cel
Zapewnić jakość kodu (testy, code review) i przygotować dokumentację + deployment.

### Scope Prac

#### J. Testing (3h)

**Tasks:**
1. Feature tests (InvoiceGenerationTest.php):
   - testCustomerCanGenerateInvoiceFromOwnAppointment
   - testAdminCanGenerateInvoiceFromAnyAppointment
   - testStaffCanGenerateInvoiceFromAssignedAppointment
   - testGuestCannotAccessInvoices
   - testRateLimitingWorks
   - testPdfHasCorrectContentType
   - testInvoiceContainsCorrectData (PDF content assertions)
2. Unit tests (InvoiceNumberGeneratorTest.php):
   - testFormatIsCorrect
   - testSequenceIsIncremental
   - testSequenceResetsPerMonth
   - testConcurrentGenerationNoDuplicates (Redis lock)
3. Policy tests (InvoiceAuthorizationTest.php):
   - testOwnerCanDownload
   - testAdminCanDownload
   - testStaffCanDownloadAssigned
   - testStaffCannotDownloadNotAssigned
   - testGuestCannotDownload

**Files Created:**
- `tests/Feature/InvoiceGenerationTest.php`
- `tests/Unit/InvoiceNumberGeneratorTest.php`
- `tests/Feature/InvoiceAuthorizationTest.php`

**Testing:**
```bash
php artisan test --filter=Invoice
```

**Target:** 95% coverage

**Deliverable:**
✅ 21 testów (10 feature + 5 unit + 6 policy)
✅ 95% test coverage
✅ Wszystkie testy green

#### K. Documentation (1.5h)

**Tasks:**
1. Utworzyć `docs/features/invoice-pdf-generation/README.md`:
   - Quick Start
   - Feature Overview
   - Benefits
   - FAQ
2. Utworzyć `docs/features/invoice-pdf-generation/installation.md`:
   - Composer dependencies
   - Migrations
   - Seeders
   - Configuration (.env variables)
   - Deployment checklist
3. Utworzyć `docs/features/invoice-pdf-generation/user-guide.md`:
   - Dla adminów:
     - Jak edytować dane firmy
     - Jak wygenerować fakturę
     - Jak wysłać email
     - Jak regenerować PDF
   - Dla klientów:
     - Jak pobrać fakturę z profilu
4. Update `CLAUDE.md`:
   - Dodać sekcję "Invoice PDF System" w "Feature Documentation"
   - Commands reference (jeśli jakieś artisan commands)
5. ADR (jeśli potrzebne):
   - `docs/decisions/ADR-XXX-invoice-pdf-architecture.md`:
     - Dlaczego DomPDF (vs Spatie PDF)?
     - Dlaczego snapshot pattern dla cen?
     - Dlaczego soft delete dla faktur?

**Files Created:**
- `docs/features/invoice-pdf-generation/README.md`
- `docs/features/invoice-pdf-generation/installation.md`
- `docs/features/invoice-pdf-generation/user-guide.md`
- `docs/decisions/ADR-XXX-invoice-pdf-architecture.md` (opcjonalnie)

**Files Modified:**
- `CLAUDE.md`

**Deliverable:**
✅ Complete documentation (README + Installation + User Guide)
✅ ADR jeśli architekturalne decyzje
✅ CLAUDE.md updated

#### L. Code Review & Deployment Prep (2.5h)

**Tasks:**
1. Self-review checklist:
   - [ ] PSR-12 coding standards (`./vendor/bin/pint`)
   - [ ] No hardcoded strings (używamy config/lang files)
   - [ ] Security: No SQL injection, XSS protection
   - [ ] Performance: N+1 queries prevention (eager loading)
   - [ ] Error handling: try/catch, user-friendly messages
   - [ ] Logging: invoice generation events
2. Deployment checklist:
   - [ ] Migrations tested (local + staging)
   - [ ] Seeders ready (InvoiceSettingSeeder)
   - [ ] .env variables documented (jeśli nowe)
   - [ ] Artisan commands documented (jeśli nowe)
   - [ ] Production readiness (error pages, fallbacks)
3. Production testing (staging):
   - [ ] Deploy to staging
   - [ ] Generate invoice (real data)
   - [ ] Download PDF (sprawdzić content)
   - [ ] Send email (sprawdzić attachment)
   - [ ] Test authorization (customer/admin/staff)
   - [ ] Test rate limiting (10 requests)
4. Rollback strategy:
   - [ ] Backup migrations (rollback commands)
   - [ ] Feature flag (jeśli potrzebne wyłączenie na produkcji)

**Files Created:**
- `docs/features/invoice-pdf-generation/deployment-checklist.md`

**Deliverable:**
✅ Code review complete (PSR-12, security, performance)
✅ Staging deployment successful
✅ Production deployment checklist OK

---

### Final Checkpoint (Dzień 10)

**Demo dla klienta:**
- Pokazać kompletny system na staging:
  1. Settings "Dane firmy" z prawdziwymi danymi
  2. Wygenerować fakturę z prawdziwej rezerwacji
  3. Pobrać PDF → sprawdzić content
  4. Wysłać email → sprawdzić Mailpit
  5. Klient pobiera fakturę z profilu

**Acceptance Criteria (WSZYSTKIE MUSZĄ BYĆ SPEŁNIONE):**
- [ ] Settings edytowalne, dane firmy poprawne
- [ ] PDF generuje się bez błędów
- [ ] Polskie znaki wyświetlają się
- [ ] Numeracja sekwencyjna działa (FV/2025/12/0001, 0002, ...)
- [ ] Authorization działa (customer/admin/staff)
- [ ] Email wysyła się z PDF załącznikiem
- [ ] Testy przechodzą (95% coverage)
- [ ] Dokumentacja kompletna

**Deployment to Production:**
- [ ] Klient akceptuje finalny system
- [ ] Deploy to production (commands dokumentowane)
- [ ] Smoke test na production (1 faktura test)
- [ ] Monitoring (Laravel Horizon, logs)

---

## Risk Management Per Faza

### Faza 1: Foundation
**Risk:** Settings validation issues
**Mitigation:** Reuse ValidNIP rule, Filament built-in validation
**Contingency:** +0.5h

### Faza 2: PDF Engine
**Risk:** Polskie znaki renderują się jako "?"
**Mitigation:** DejaVu Sans font, early testing (dzień 4 demo)
**Contingency:** +1h (fallback to plain HTML invoice)

### Faza 3: Filament Integration
**Risk:** Filament v4 namespace issues (Infolists vs Forms)
**Mitigation:** Follow CLAUDE.md guidelines, use existing ViewAppointment patterns
**Contingency:** +0.5h

### Faza 4: Automation
**Risk:** Queue job failures (Redis down)
**Mitigation:** Graceful error handling, fallback to sync email
**Contingency:** +0.5h

### Faza 5: QA
**Risk:** Test failures na staging
**Mitigation:** Early testing per faza, comprehensive test suite
**Contingency:** +1h (debug + fix)

**Total Contingency:** 3.5h (included in 10% buffer)

---

## Daily Standup Protocol

### Format (5 minut via Slack/email)

**Dzień X - Status Update:**
- **Yesterday:** [co zostało zrobione]
- **Today:** [co będzie robione]
- **Blockers:** [jeśli jakieś problemy]
- **ETA:** [on track / delayed / ahead]

**Example:**
```
Dzień 3 - Status Update:
- Yesterday: Settings system complete, Invoice models created
- Today: InvoiceNumberGenerator implementation + Redis locking
- Blockers: None
- ETA: On track (Checkpoint #1 passed ✅)
```

---

## Checkpoints Summary

| Checkpoint | Dzień | Deliverable | Acceptance Criteria |
|------------|-------|-------------|---------------------|
| **#1** | 2 | Foundation ready | Settings edytowalne, Invoice models działa |
| **#2** | 4 | PDF generation working | PDF renderuje się, polskie znaki OK, layout zaakceptowany |
| **#3** | 6 | Filament integration complete | Admin może generować, CRUD działa, customer może pobierać |
| **#4** | 7 | Email automation working | Email wysyła się z PDF attachment |
| **Final** | 10 | Production ready | Wszystkie AC spełnione, deployment OK |

---

**Prepared by:** Project Coordinator
**Date:** 24 grudnia 2024
**Version:** 1.0
**Branch:** feature/invoice-pdf-estimation
