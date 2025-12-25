# Harmonogram Implementacji: 5 Faz - DWA WARIANTY

**Projekt:** System Generowania Faktur PDF
**Branch:** feature/invoice-pdf-estimation
**Data:** 24 grudnia 2024

---

## Wybór Wariantu

Przygotowałem **DWA harmonogramy** - w zależności od tego, czy wykorzystujemy wcześniejszy kod czy robimy wszystko od zera.

### 🎯 WARIANT A: Implementacja Od Zera (POLECAM)

**Total Effort:** 45-50h
**Timeline:** 12-14 dni roboczych (4h/dzień avg)
**Założenie:** NIE wykorzystujemy `feature/invoice-system-with-estimate-agent`

**Krótkie podsumowanie:**
- Tydzień 1 (Dni 1-7): Foundation + PDF Engine + część Filament (30h)
- Tydzień 2 (Dni 8-14): Filament cont. + Email + Testing (19h)

**Checkpointy:**
- Dzień 4: Foundation ready
- Dzień 7: PDF generation working
- Dzień 11: Filament integration complete
- Dzień 14: Production ready

---

### 💡 WARIANT B: Z Wcześniejszym Kodem (Opcjonalny) ⭐

**Total Effort:** 30h
**Timeline:** 10 dni roboczych (3h/dzień avg)
**Założenie:** MERGE `feature/invoice-system-with-estimate-agent` PRZED rozpoczęciem

**Krótkie podsumowanie:**
- Tydzień 1 (Dni 1-5): Merge + Settings + PDF Engine (17h)
- Tydzień 2 (Dni 6-10): Filament + Email + Testing (12.5h)

**Checkpointy:**
- Dzień 2: Merge verified + Settings ready
- Dzień 5: PDF generation working
- Dzień 7: Filament integration complete
- Dzień 10: Production ready

---

**WAŻNE:** Jeśli klient nie zdecyduje się na merge przed rozpoczęciem prac - automatycznie Wariant A.

---

# WARIANT A: Implementacja Od Zera (45-50h, 12-14 dni)

## Przegląd Faz - WARIANT A

| Faza | Scope | Godziny | Dni | Checkpoints |
|------|-------|---------|-----|-------------|
| **1. Foundation** | UserProfile + ValidNIP + Settings + Models | 14h | 1-4 | UserProfile working, Models ready |
| **2. PDF Engine** | Number Generator + PDF Template | 16h | 5-7 | PDF generation working, przykład |
| **3. Filament** | InvoiceResource + AppointmentResource + Customer Panel | 8h | 8-11 | CRUD faktur, actions working |
| **4. Email** | Mailable + Queue Job | 5h | 11-12 | Email z PDF wysyłany |
| **5. QA** | Testing + Docs + Code Review | 6h | 12-14 | 95% coverage, production ready |

**Total:** 49h → zaokrąglone do **45-50h**

---

## Faza 1: Foundation (Dni 1-4, 14h) - WARIANT A

### Cel
Zbudować fundament systemu OD ZERA: zbieranie danych NIP od klientów, walidacja, Settings dla ParaDocks, Invoice models.

### Scope Prac

#### A. UserInvoiceProfile Model + UI w Booking Wizard (6h)

**Tasks:**
1. Utworzyć `app/Models/UserInvoiceProfile.php`:
   - Fields: user_id, nip, company_name, address_street, address_city, address_postal_code
   - Relation: belongsTo(User)
   - Validation w model (unique NIP)
2. Migracja `create_user_invoice_profiles_table.php`:
   - Index on nip (unique), user_id
3. Update booking wizard (Step 4 - Contact Info):
   - Checkbox "Potrzebuję faktury" (Alpine.js reactivity)
   - Conditional form (NIP, Nazwa firmy, Adres - 3 pola)
   - Frontend validation (NIP format: 10 cyfr)
4. Snapshot w `appointments`:
   - Migracja: dodać kolumny `invoice_requested`, `invoice_nip`, `invoice_company_name`, `invoice_address`
   - BookingController: save snapshot on booking submit
5. Blade templates:
   - Update `booking-wizard/steps/contact-info.blade.php`
   - Alpine.js dla conditional rendering

**Files Created:**
- `app/Models/UserInvoiceProfile.php`
- `database/migrations/YYYY_MM_DD_create_user_invoice_profiles_table.php`
- `database/migrations/YYYY_MM_DD_add_invoice_fields_to_appointments.php`
- `database/factories/UserInvoiceProfileFactory.php`

**Files Modified:**
- `resources/views/booking-wizard/steps/contact-info.blade.php`
- `app/Http/Controllers/BookingController.php`

**Testing:**
- Feature: Booking with invoice data saves to appointments
- Unit: UserInvoiceProfile relationship works

**Deliverable:**
✅ Checkbox "Potrzebuję faktury" w booking wizard
✅ Formularz NIP/firma/adres conditional
✅ Snapshot invoice_* w appointments table

---

#### B. ValidNIP Rule (2h)

**Tasks:**
1. Utworzyć `app/Rules/ValidNIP.php`:
   - Checksum mod 11 algorithm (polish NIP validation)
   - Validation logic: format + checksum
   - Error messages PL + EN
2. Lang files:
   - `lang/pl/validation.php`: "Nieprawidłowy NIP (błędna suma kontrolna)"
   - `lang/en/validation.php`: "Invalid NIP (incorrect checksum)"
3. Unit tests (10 scenarios):
   - Valid NIP: `1234567890`
   - Valid with checksum
   - Invalid: too short, too long, contains letters, wrong checksum
   - Edge cases: null, empty, whitespace

**Files Created:**
- `app/Rules/ValidNIP.php`
- `tests/Unit/ValidNIPTest.php`

**Files Modified:**
- `lang/pl/validation.php`
- `lang/en/validation.php`

**Testing:**
- Unit: 10 test scenarios (all green)

**Deliverable:**
✅ ValidNIP rule with checksum mod 11
✅ Error messages PL + EN
✅ 10 unit tests passing

---

#### C. Settings System (3h)

**Tasks:**
1. Rozszerzyć `app/Filament/Pages/SystemSettings.php`:
   - Nowy tab "Dane firmy" (`invoiceTab()`)
   - Form fields: company_name, nip (ValidNIP rule), regon, address (4 pola), bank_account
   - Logo upload (FileUpload component, PNG/JPG, max 2MB)
2. Settings keys w `system_settings` table:
   - `invoice.company_name`
   - `invoice.company_nip`
   - `invoice.company_regon`
   - `invoice.company_address`
   - `invoice.company_bank_account`
   - `invoice.company_logo` (file path)
3. Seeders:
   - `InvoiceSettingSeeder.php` z placeholder danymi

**Files Created:**
- `database/seeders/InvoiceSettingSeeder.php`

**Files Modified:**
- `app/Filament/Pages/SystemSettings.php`

**Testing:**
- Manual: `/admin/system-settings` → tab "Dane firmy" → edycja → save

**Deliverable:**
✅ Settings tab "Dane firmy" edytowalny
✅ Logo upload + preview working
✅ ValidNIP rule reused w Settings

---

#### D. Invoice Models + Database (3h)

**Tasks:**
1. Utworzyć `app/Models/Invoice.php`:
   - Fields: number (unique), issue_date, sale_date, appointment_id, customer_id
   - Seller data snapshot: seller_name, seller_nip, seller_regon, seller_address, seller_bank_account
   - Buyer data snapshot: buyer_name, buyer_nip, buyer_address
   - Totals: total_net, total_vat, total_gross (DECIMAL 10,2)
   - Timestamps, soft deletes
   - Relation: belongsTo(Appointment), belongsTo(User), hasMany(InvoiceItems)
2. Utworzyć `app/Models/InvoiceItem.php`:
   - Fields: invoice_id, name, quantity, unit_price_net, vat_rate (INT default 23)
   - Calculated: total_net, total_vat, total_gross
   - Relation: belongsTo(Invoice)
3. Migracje:
   - `create_invoices_table.php` (indexes: appointment_id, issue_date)
   - `create_invoice_items_table.php` (index: invoice_id)
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
- Unit: Invoice factory creates valid records
- Unit: InvoiceItem relationship works

**Deliverable:**
✅ Tabele invoices + invoice_items w DB
✅ Models z relacjami działające
✅ Factories ready for tests

---

### Checkpoint #1 (Po Dniu 4) - WARIANT A

**Demo dla klienta:**
- UserInvoiceProfile działa (pokazać w Tinker: `UserInvoiceProfile::factory()->create()`)
- Booking wizard ma checkbox + formularz NIP
- Settings "Dane firmy" edytowalne
- Invoice models ready (factory test)

**Kryteria akceptacji:**
- [ ] Booking wizard z checkbox + NIP form working
- [ ] ValidNIP rule waliduje poprawnie (10 tests green)
- [ ] Settings "Dane firmy" zapisuje dane
- [ ] Logo upload działa
- [ ] Invoice models + migrations bez błędów
- [ ] Wszystkie relacje (User, Appointment, Invoice, InvoiceItem) działają

---

## Faza 2: PDF Engine (Dni 5-7, 16h) - WARIANT A

### Cel
Zaimplementować core PDF generation: sekwencyjna numeracja + rendering Blade → PDF.

### Scope Prac

#### E. InvoiceNumberGenerator Service (3h)

**Tasks:**
1. Utworzyć `app/Services/Invoice/InvoiceNumberGenerator.php`:
   - Method: `generate(Appointment $appointment): string`
   - Format: `FV/YYYY/MM/XXXX` (e.g., FV/2025/12/0001)
   - Redis locking: `Cache::lock("invoice_lock_{year}_{month}", 10)`
   - Query DB: `SELECT MAX(number) FROM invoices WHERE YEAR(created_at)=X AND MONTH(created_at)=Y`
   - Increment + 1, pad to 4 digits (str_pad 0001)
2. Config:
   - `config/invoice.php`: format template, prefix "FV"
3. Integration:
   - Invoice model: auto-generate number w `creating` event

**Files Created:**
- `app/Services/Invoice/InvoiceNumberGenerator.php`
- `config/invoice.php`

**Files Modified:**
- `app/Models/Invoice.php` (event listener)

**Testing:**
- Unit: Format correct (FV/2025/12/0001)
- Unit: Sequence incremental (0001, 0002, 0003)
- Unit: Month reset (Dec 0099 → Jan 0001)
- Integration: Concurrent generation (2 processes, no duplicates via Redis lock)

**Deliverable:**
✅ Numeracja sekwencyjna FV/YYYY/MM/XXXX
✅ Redis locking zapobiega duplikatom
✅ Auto-generate on Invoice::create()

---

#### F. InvoicePdfGenerator Service + Template (10h)

**Tasks:**
1. Install DomPDF:
   ```bash
   composer require barryvdh/laravel-dompdf
   ```
2. Utworzyć `app/Services/Invoice/InvoicePdfGenerator.php`:
   - Method: `generate(Invoice $invoice): string` (returns PDF binary)
   - Load Settings (company data)
   - Load Invoice + InvoiceItems (eager loading)
   - Render Blade → PDF using DomPDF
3. Utworzyć `resources/views/pdf/invoice.blade.php`:
   - **Header:**
     - Logo firmy (left, 150px width from Settings logo)
     - Dane sprzedawcy (right): Nazwa, NIP, REGON, Adres
   - **Title:**
     - "FAKTURA VAT"
     - Numer: FV/2025/12/0001
     - Data wystawienia, Data sprzedaży
   - **Nabywca:**
     - Dane klienta (from invoice.buyer_*)
   - **Tabela usług:**
     - Lp. | Nazwa | Ilość | Cena netto | VAT % | Kwota VAT | Cena brutto
     - Każda pozycja z InvoiceItems
   - **Podsumowanie:**
     - Suma netto
     - Suma VAT (23%)
     - **DO ZAPŁATY** (bold, duża czcionka)
   - **Footer:**
     - Numer konta bankowego (from Settings)
     - Termin płatności: 7 dni od wystawienia
     - Podpis (placeholder)
4. Styling:
   - Tailwind CSS inline styles (DomPDF compatibility)
   - Table-based layout (NO flexbox/grid - DomPDF limitation)
   - DejaVu Sans font (polskie znaki: ąćęłńóśźż)
   - Polish number formatting: "1 234,56 zł" (space separator, comma decimal)
5. VAT calculations:
   - Helper method: `calculateVAT(float $brutto, int $rate): array`
   - Returns: ['netto', 'vat', 'brutto']
   - Netto = Brutto / 1.23
   - VAT = Brutto - Netto

**Files Created:**
- `app/Services/Invoice/InvoicePdfGenerator.php`
- `resources/views/pdf/invoice.blade.php`
- `app/Helpers/VATCalculator.php` (helper)

**Files Modified:**
- `config/invoice.php` (VAT rate 23%, PDF settings)

**Testing:**
- Feature: PDF generates without errors
- Feature: PDF contains correct invoice number
- Feature: Polish characters display correctly (ąćęłńóśźż)
- Manual: Open PDF → sprawdzić layout, logo, polskie znaki

**Deliverable:**
✅ PDF faktur generowany poprawnie
✅ Polskie znaki wyświetlają się
✅ Layout profesjonalny, zgodny z Art. 106e VAT
✅ Logo firmy w header

---

#### G. Storage + Download (3h)

**Tasks:**
1. Controller `app/Http/Controllers/InvoiceController.php`:
   - Method: `download(Invoice $invoice)`
   - Authorization: InvoicePolicy::download (owner/admin/staff)
   - Load invoice + items (eager loading)
   - Generate PDF (InvoicePdfGenerator)
   - Return streaming response (Content-Disposition: attachment)
2. Route:
   ```php
   Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
       ->middleware(['auth', 'throttle:invoice'])
       ->name('invoices.download');
   ```
3. Rate limiting:
   - `config/app.php`: `'invoice' => '10,1'` (10 requests per minute)
4. Policy:
   - `app/Policies/AppointmentPolicy.php`: add `downloadInvoice()` method
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
- Feature: Customer cannot download other's invoice (403 Forbidden)
- Feature: Admin can download any invoice
- Feature: Rate limiting (429 after 10 requests)
- Feature: PDF has correct Content-Type header (application/pdf)

**Deliverable:**
✅ Endpoint `/invoices/{id}/download` działający
✅ Authorization policy working
✅ Rate limiting zapobiega abuse
✅ PDF streaming response (no disk storage)

---

### Checkpoint #2 (Po Dniu 7) - WARIANT A

**Demo dla klienta:**
- Wygenerować przykładową fakturę (z testowymi danymi lub rzeczywistymi z Settings)
- Pokazać PDF: layout, logo, polskie znaki, wszystkie wymagane pola
- Potwierdzić akceptację wyglądu

**Kryteria akceptacji:**
- [ ] PDF generuje się bez błędów
- [ ] Polskie znaki wyświetlają się poprawnie (ąćęłńóśźż)
- [ ] Logo firmy wyświetla się w header
- [ ] Wszystkie wymagane elementy VAT na fakturze (NIP, data, pozycje, sumy)
- [ ] Obliczenia VAT poprawne (brutto - netto = VAT)
- [ ] Numeracja sekwencyjna działa (FV/2025/12/0001, 0002, ...)

**IMPORTANT:** Klient musi zaakceptować layout PDF przed przejściem do Fazy 3. Jeśli są uwagi - poprawki w ramach tej fazy (contingency: max +2h).

---

## Faza 3: Filament Admin Panel + UI (Dni 8-11, 8h) - WARIANT A

### Cel
Zintegrować PDF generation z panelem admina: CRUD faktur, action w ViewAppointment, button w customer panel.

### Scope Prac

#### H. InvoiceResource (4h)

**Tasks:**
1. Utworzyć `app/Filament/Resources/InvoiceResource.php`:
   - **ListInvoices:**
     - Kolumny: Number, Customer (name), Issue Date, Total (formatted PLN)
     - Filters: DateRangePicker (issue_date), TextInput (customer search)
     - Actions: View, Delete (soft delete)
     - Sort: newest first (created_at DESC)
     - Pagination: 25 per page
2. Utworzyć `app/Filament/Resources/InvoiceResource/Pages/ViewInvoice.php`:
   - **Infolists** (Filament v4):
     - Sekcja "Dane faktury": Number, Issue Date, Sale Date
     - Sekcja "Sprzedawca": Name, NIP, REGON, Address
     - Sekcja "Nabywca": Name, NIP, Address
     - Sekcja "Pozycje": Table (InvoiceItems - nazwa, ilość, cena, VAT)
     - Sekcja "Podsumowanie": Net, VAT, **Gross** (bold, money format)
   - **Header Actions:**
     - "Pobierz PDF" (download icon, green)
     - "Wyślij email" (mail icon, blue) - Faza 4
     - "Regeneruj PDF" (refresh icon, gray) - future
3. Authorization:
   - `app/Policies/InvoicePolicy.php`:
     - viewAny: admin + staff
     - view: admin + staff (assigned to appointment only)
     - delete: admin only

**Files Created:**
- `app/Filament/Resources/InvoiceResource.php`
- `app/Filament/Resources/InvoiceResource/Pages/ListInvoices.php`
- `app/Filament/Resources/InvoiceResource/Pages/ViewInvoice.php`
- `app/Policies/InvoicePolicy.php`

**Testing:**
- Feature: List invoices (admin widzi wszystkie, staff tylko assigned)
- Feature: View invoice (infolist wyświetla poprawne dane)
- Feature: Delete invoice (soft delete, nie hard delete)

**Deliverable:**
✅ Panel CRUD faktur w `/admin/invoices`
✅ Filtrowanie po dacie i customer
✅ ViewInvoice z Infolists
✅ Header actions (Download/Email/Regenerate)

---

#### I. AppointmentResource Integration (2h)

**Tasks:**
1. Update `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php`:
   - **Header action "Wygeneruj fakturę":**
     - Visible when: `invoice_requested=true && !appointment->invoice`
     - Walidacja:
       - Check invoice_* fields complete (NIP, company_name, address)
       - Error notification jeśli brakuje danych
     - Action logic:
       - Create Invoice record (InvoiceNumberGenerator auto-generates number)
       - Create InvoiceItem (from appointment.service)
       - Redirect to ViewInvoice
       - Success toast: "Faktura FV/2025/12/0001 wygenerowana"
   - **Info badge** (jeśli faktura już istnieje):
     - "Faktura: FV/2025/12/0001" (link do ViewInvoice)

**Files Modified:**
- `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php`

**Testing:**
- Feature: Generate invoice from appointment (creates Invoice record)
- Feature: Cannot generate when invoice_requested=false
- Feature: Redirect to ViewInvoice after generation
- Feature: Cannot generate duplicate (invoice already exists)

**Deliverable:**
✅ Action "Wygeneruj fakturę" w ViewAppointment
✅ Walidacja przed generowaniem
✅ Toast notification po sukcesie
✅ Redirect do ViewInvoice

---

#### J. Customer Panel Integration (2h)

**Tasks:**
1. Update `resources/views/profile/appointments/show.blade.php` (lub odpowiednik):
   - **Conditional button "Pobierz fakturę":**
     ```blade
     @if($appointment->invoice && $appointment->invoice_requested)
         <a href="{{ route('invoices.download', $appointment->invoice) }}"
            target="_blank"
            class="btn btn-primary">
             <svg>...</svg> Pobierz fakturę PDF
         </a>
     @endif
     ```
   - Icon: Heroicon document-download
   - Target: `_blank` (nowa karta)

**Files Modified:**
- `resources/views/profile/appointments/show.blade.php`

**Testing:**
- Feature: Customer widzi przycisk gdy invoice exists
- Feature: Kliknięcie otwiera PDF w nowej karcie
- Feature: Authorization: customer can download only own invoice

**Deliverable:**
✅ Przycisk "Pobierz fakturę" w customer panel
✅ Conditional display (tylko gdy invoice istnieje)
✅ PDF otwiera się w nowej karcie

---

### Checkpoint #3 (Po Dniu 11) - WARIANT A

**Demo dla klienta:**
- Pokazać pełny workflow:
  1. Admin otwiera ViewAppointment (z invoice_requested=true)
  2. Klika "Wygeneruj fakturę" → faktura tworzy się, redirect do ViewInvoice
  3. Klient loguje się → widzi przycisk "Pobierz fakturę" → klika → PDF się otwiera
  4. Lista faktur w `/admin/invoices` działa (filtrowanie, widok)

**Kryteria akceptacji:**
- [ ] Admin może wygenerować fakturę z ViewAppointment
- [ ] Lista faktur w `/admin/invoices` działa
- [ ] ViewInvoice wyświetla wszystkie dane poprawnie (Infolists)
- [ ] Klient może pobrać fakturę z profilu
- [ ] Authorization działa (customer/admin/staff)
- [ ] Rate limiting działa (429 po 10 requestach)

---

## Faza 4: Email + Automation (Dni 11-12, 5h) - WARIANT A

### Cel
Dodać automatyczne wysyłanie faktur emailem z PDF załącznikiem (queue job).

### Scope Prac

#### K. Email Notification (3h)

**Tasks:**
1. Utworzyć `app/Mail/InvoiceGenerated.php`:
   - Subject (PL): "Twoja faktura FV/2025/12/0001 - ParaDocks"
   - Subject (EN): "Your invoice FV/2025/12/0001 - ParaDocks"
   - Attachment: PDF (generated on-the-fly via InvoicePdfGenerator)
   - Template: `resources/views/emails/invoice-generated-{pl|en}.blade.php`
   - Content:
     - Podziękowanie za rezerwację
     - Informacja o fakturze (numer, kwota)
     - Link do pobrania: `{{ route('invoices.download', $invoice) }}`
     - Załącznik PDF
2. Utworzyć `app/Jobs/SendInvoiceEmailJob.php`:
   - Dispatch to queue: redis (high priority)
   - Generate PDF binary
   - Send email with attachment
   - Log to `email_sends` table (existing feature)
   - Retries: 3 attempts
3. Update ViewInvoice header action:
   - "Wyślij email" → dispatch SendInvoiceEmailJob
   - Success notification: "Email wysłany do kolejki"
4. Blade template:
   - `resources/views/emails/invoice-generated-pl.blade.php`
   - `resources/views/emails/invoice-generated-en.blade.php`
   - Reuse existing email layout

**Files Created:**
- `app/Mail/InvoiceGenerated.php`
- `app/Jobs/SendInvoiceEmailJob.php`
- `resources/views/emails/invoice-generated-pl.blade.php`
- `resources/views/emails/invoice-generated-en.blade.php`

**Files Modified:**
- `app/Filament/Resources/InvoiceResource/Pages/ViewInvoice.php` (header action)

**Testing:**
- Feature: Email sent with PDF attachment
- Feature: Email logged in email_sends table
- Queue: Job dispatched to Redis queue
- Manual: Sprawdzić Mailpit (http://paradocks.local:8025)
- Manual: PDF attachment otwiera się

**Deliverable:**
✅ Email z PDF załącznikiem wysyłany
✅ Queue job (async, non-blocking)
✅ Header action "Wyślij email" w ViewInvoice
✅ Email templates PL + EN

---

### Checkpoint #4 (Po Dniu 12) - WARIANT A

**Demo dla klienta:**
- Pokazać kompletny system:
  1. Wygenerować fakturę
  2. Kliknąć "Wyślij email"
  3. Sprawdzić Mailpit → email przyszedł z załącznikiem PDF
  4. Otworzyć załącznik → PDF poprawny

**Kryteria akceptacji:**
- [ ] Email wysyła się poprawnie
- [ ] PDF attachment jest poprawny (można otworzyć)
- [ ] Email template profesjonalny (PL/EN)
- [ ] Queue job nie blokuje UI (async)
- [ ] Email logi w email_sends table

**IMPORTANT:** To finalny checkpoint przed testami. Klient musi zaakceptować cały system jako kompletny i działający.

---

## Faza 5: QA & Deployment (Dni 12-14, 6h) - WARIANT A

### Cel
Zapewnić jakość kodu (testy, code review) i przygotować deployment na production.

### Scope Prac

#### M. Testing (3h)

**Tasks:**
1. Feature tests (`tests/Feature/InvoiceGenerationTest.php`) - 12 cases:
   - Admin can generate invoice from appointment
   - Customer cannot generate invoice (only admin)
   - Generated invoice has correct number format (FV/YYYY/MM/XXXX)
   - Invoice totals calculated correctly (net + VAT = gross)
   - PDF download requires authentication
   - Customer can download own invoice
   - Customer cannot download other's invoice (403)
   - Staff can download invoice from assigned appointment
   - Staff cannot download from not assigned (403)
   - Rate limiting works (11th request = 429)
   - PDF has correct Content-Type header
   - PDF contains invoice number (assertion on binary)
2. Unit tests (`tests/Unit/InvoiceNumberGeneratorTest.php`) - 5 cases:
   - Format: FV/YYYY/MM/XXXX
   - Sequential: 0001, 0002, 0003
   - Reset per month (Jan = 0001, Feb = 0001)
   - Concurrent generation (2 processes, no duplicates via Redis)
   - Redis timeout (exception thrown)
3. Policy tests (`tests/Feature/InvoiceAuthorizationTest.php`) - 6 cases:
   - Owner can download
   - Admin can download any
   - Staff can download assigned
   - Staff cannot download not assigned
   - Guest cannot download (redirect to login)
   - Other customer cannot download

**Files Created:**
- `tests/Feature/InvoiceGenerationTest.php`
- `tests/Unit/InvoiceNumberGeneratorTest.php`
- `tests/Feature/InvoiceAuthorizationTest.php`

**Testing:**
```bash
php artisan test --filter=Invoice
```

**Target:** 95% code coverage

**Deliverable:**
✅ 23 tests pass (12 feature + 5 unit + 6 policy)
✅ 95% test coverage
✅ All tests green

---

#### N. Documentation (2h)

**Tasks:**
1. Utworzyć `docs/features/invoice-pdf-generation/README.md`:
   - Feature Overview
   - Business Benefits (oszczędność czasu, eliminacja błędów)
   - Quick Start
   - FAQ
2. Utworzyć `docs/features/invoice-pdf-generation/INSTALLATION.md`:
   - Composer dependencies: `composer require barryvdh/laravel-dompdf`
   - Migrations: `php artisan migrate`
   - Seeders: `php artisan db:seed --class=InvoiceSettingSeeder`
   - Konfiguracja Settings (dane firmy, logo)
   - Deployment checklist
3. Utworzyć `docs/features/invoice-pdf-generation/USER_GUIDE.md`:
   - Dla adminów: Jak wygenerować, wysłać email, edytować dane firmy
   - Dla klientów: Jak pobrać fakturę z profilu
4. ADR (jeśli potrzebne):
   - `docs/decisions/ADR-XXX-invoice-pdf-architecture.md`
   - Decyzje: DomPDF vs Spatie PDF, On-the-fly vs Storage, Snapshot pattern
5. Update `CLAUDE.md`:
   - Dodać w sekcji "Feature Documentation": Invoice PDF Generation
   - Link do README
   - Commands reference (seeders)

**Files Created:**
- `docs/features/invoice-pdf-generation/README.md`
- `docs/features/invoice-pdf-generation/INSTALLATION.md`
- `docs/features/invoice-pdf-generation/USER_GUIDE.md`
- `docs/decisions/ADR-XXX-invoice-pdf-architecture.md` (optional)

**Files Modified:**
- `CLAUDE.md`

**Deliverable:**
✅ Complete documentation (3 docs)
✅ ADR jeśli architekturalne decyzje
✅ CLAUDE.md updated

---

#### O. Code Review + Deployment Prep (1h)

**Tasks:**
1. Self-review checklist:
   - PSR-12 coding standards (`./vendor/bin/pint`)
   - No hardcoded strings (config/lang files)
   - Security: No SQL injection, XSS protection (Blade {{ }})
   - Performance: N+1 queries prevention (eager loading)
   - Error handling: try/catch, user-friendly messages
2. Deployment checklist:
   - Migrations tested (local + staging)
   - Seeders ready (InvoiceSettingSeeder)
   - .env variables documented (no new vars needed)
   - All tests pass (23/23 green)
3. Production readiness:
   - Logging: invoice generation events (`Log::info()`)
   - Rollback strategy: migration down() works
   - Error handling: graceful failures (toast notifications)

**Deliverable:**
✅ Code review complete (PSR-12, security, performance OK)
✅ Deployment checklist ready
✅ Production-ready code

---

### Final Checkpoint (Dzień 14) - WARIANT A

**Demo dla klienta:**
- Pokazać kompletny system na staging:
  1. Settings "Dane firmy" z prawdziwymi danymi
  2. Wygenerować fakturę z prawdziwej rezerwacji
  3. Pobrać PDF → sprawdzić content (wszystkie dane poprawne)
  4. Wysłać email → sprawdzić Mailpit
  5. Klient pobiera fakturę z profilu

**Acceptance Criteria (WSZYSTKIE MUSZĄ BYĆ SPEŁNIONE):**
- [ ] Settings edytowalne, dane firmy poprawne
- [ ] PDF generuje się bez błędów
- [ ] Polskie znaki wyświetlają się
- [ ] Numeracja sekwencyjna działa (FV/2025/12/0001, 0002, ...)
- [ ] Authorization działa (customer/admin/staff)
- [ ] Email wysyła się z PDF załącznikiem
- [ ] Testy przechodzą (23/23 green, 95% coverage)
- [ ] Dokumentacja kompletna (3 docs)

**Deployment to Production:**
- [ ] Klient akceptuje finalny system
- [ ] Deploy to production (commands dokumentowane)
- [ ] Smoke test (1 faktura test)
- [ ] Monitoring (Laravel Horizon, logs)

---

# WARIANT B: Z Wcześniejszym Kodem (30h, 10 dni)

## Przegląd Faz - WARIANT B

**WAŻNE:** Wymaga merge `feature/invoice-system-with-estimate-agent` do `develop` PRZED rozpoczęciem.

| Faza | Scope | Godziny | Dni | Checkpoints |
|------|-------|---------|-----|-------------|
| **1. Merge + Settings** | Verification + Settings dla ParaDocks | 3h | 1-2 | Merge OK, Settings ready |
| **2. Invoice Models + PDF** | Models + Number Generator + PDF Generator | 14h | 2-5 | PDF generation working |
| **3. Filament** | InvoiceResource + Integrations | 6h | 6-7 | CRUD faktur, actions working |
| **4. Email** | Mailable + Queue Job | 4h | 7-8 | Email z PDF wysyłany |
| **5. QA** | Testing + Docs + Code Review | 2.5h | 8-10 | Production ready |

**Total:** 29.5h → zaokrąglone do **30h**

---

## Faza 1: Merge Verification + Settings (Dni 1-2, 3h) - WARIANT B

### Cel
Zmergować wcześniejszy kod i zweryfikować że wszystko działa, dodać Settings dla ParaDocks.

### Scope Prac

#### A. Merge Verification (1h)

**Tasks:**
1. Git merge:
   ```bash
   git checkout develop
   git pull origin feature/invoice-system-with-estimate-agent
   # Resolve conflicts jeśli są
   ```
2. Run existing tests (36 tests):
   ```bash
   php artisan test --filter=UserInvoiceProfile
   php artisan test --filter=ValidNIP
   ```
   - Wszystkie muszą przejść ✅
3. Verify functionality:
   - UserInvoiceProfile model działa (factory test)
   - ValidNIP rule waliduje poprawnie (checksum mod 11)
   - UI w booking wizard wyświetla formularz NIP
   - Snapshot invoice_* zapisuje dane w appointments

**Files Modified:**
- Potencjalne conflicts resolution

**Testing:**
- REUSE 36 existing tests (all green)

**Deliverable:**
✅ Merge successful (no conflicts)
✅ 36 existing tests passing
✅ UserInvoiceProfile + ValidNIP + UI verified working

---

#### B. Settings System (2h)

**Tasks:**
1. Rozszerzyć `app/Filament/Pages/SystemSettings.php`:
   - TAKIE SAME pola jak Wariant A (company_name, nip, regon, address, bank_account, logo)
2. Settings keys w `system_settings`
3. Seeders: `InvoiceSettingSeeder.php`

**Files Created:**
- `database/seeders/InvoiceSettingSeeder.php`

**Files Modified:**
- `app/Filament/Pages/SystemSettings.php`

**Testing:**
- Manual: `/admin/system-settings` → tab "Dane firmy"

**Deliverable:**
✅ Settings tab "Dane firmy" edytowalny
✅ Logo upload working
✅ ValidNIP rule reused

**Oszczędność vs Wariant A:** 1h (faster with existing ValidNIP rule + Filament patterns)

---

### Checkpoint #1 (Po Dniu 2) - WARIANT B

**Demo dla klienta:**
- Merge verified (36 tests green)
- UserInvoiceProfile działa
- Booking wizard ma formularz NIP
- Settings "Dane firmy" edytowalne

**Kryteria akceptacji:**
- [ ] Merge successful (zero conflicts lub resolved)
- [ ] 36 existing tests passing
- [ ] UserInvoiceProfile model working
- [ ] ValidNIP rule working (checksum mod 11)
- [ ] UI w booking wizard wyświetla formularz
- [ ] Settings "Dane firmy" zapisuje dane + logo

---

## Faza 2: Invoice Models + PDF Generation (Dni 2-5, 14h) - WARIANT B

### Scope Prac

#### C. Invoice Models + Database (2.5h)

**Tasks:**
- TAKIE SAME jak Wariant A (Invoice + InvoiceItem models, migracje, relacje)

**Files Created:**
- `app/Models/Invoice.php`
- `app/Models/InvoiceItem.php`
- 2 migracje
- 2 factories

**Files Modified:**
- `app/Models/Appointment.php`

**Deliverable:**
✅ Invoice models ready
✅ Migracje bez błędów

**Oszczędność vs Wariant A:** 0.5h (existing test patterns, faster factory setup)

---

#### D. InvoiceNumberGenerator (3h)

**Tasks:**
- TAKIE SAME jak Wariant A (Redis lock, FV/YYYY/MM/XXXX)

**Files Created:**
- `app/Services/Invoice/InvoiceNumberGenerator.php`
- `config/invoice.php`

**Deliverable:**
✅ Numeracja sekwencyjna FV/YYYY/MM/XXXX
✅ Redis locking

**Oszczędność vs Wariant A:** 0h (no reuse możliwy)

---

#### E. InvoicePdfGenerator (8.5h)

**Tasks:**
- TAKIE SAME jak Wariant A (DomPDF + Blade template + polskie znaki + VAT calculations)

**Files Created:**
- `app/Services/Invoice/InvoicePdfGenerator.php`
- `resources/views/pdf/invoice.blade.php`

**Deliverable:**
✅ PDF generation working
✅ Polskie znaki OK
✅ Layout profesjonalny

**Oszczędność vs Wariant A:** 1.5h (faster Composer setup, existing project patterns)

---

### Checkpoint #2 (Po Dniu 5) - WARIANT B

**Demo dla klienta:**
- PDF generuje się poprawnie
- Polskie znaki wyświetlają się
- Layout zaakceptowany

**Kryteria akceptacji:**
- TAKIE SAME jak Wariant A Checkpoint #2

---

## Faza 3: Filament Integration (Dni 6-7, 6h) - WARIANT B

### Scope Prac

#### F. InvoiceResource (3h)

**Tasks:**
- TAKIE SAME jak Wariant A (List + View + Actions)

**Files Created:**
- `app/Filament/Resources/InvoiceResource.php` + 2 pages
- `app/Policies/InvoicePolicy.php`

**Deliverable:**
✅ CRUD faktur w `/admin/invoices`

**Oszczędność vs Wariant A:** 1h (existing Filament resource patterns)

---

#### G. AppointmentResource Integration (1.5h)

**Tasks:**
- TAKIE SAME jak Wariant A (header action "Wygeneruj fakturę")

**Files Modified:**
- `app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php`

**Deliverable:**
✅ Action working

**Oszczędność vs Wariant A:** 0.5h (existing action patterns)

---

#### H. Customer Panel Integration (1.5h)

**Tasks:**
- TAKIE SAME jak Wariant A (przycisk "Pobierz fakturę")

**Files Modified:**
- `resources/views/profile/appointments/show.blade.php`

**Deliverable:**
✅ Przycisk working

**Oszczędność vs Wariant A:** 0.5h

---

### Checkpoint #3 (Po Dniu 7) - WARIANT B

**Kryteria akceptacji:**
- TAKIE SAME jak Wariant A Checkpoint #3

---

## Faza 4: Email + Automation (Dni 7-8, 4h) - WARIANT B

### Scope Prac

#### I. Email Notification (2.5h)

**Tasks:**
- TAKIE SAME jak Wariant A (Mailable + queue job + PDF attachment)

**Files Created:**
- `app/Mail/InvoiceGenerated.php`
- `app/Jobs/SendInvoiceEmailJob.php`
- 2 email templates

**Deliverable:**
✅ Email z PDF wysyłany

**Oszczędność vs Wariant A:** 0.5h (existing email system patterns)

---

### Checkpoint #4 (Po Dniu 8) - WARIANT B

**Kryteria akceptacji:**
- TAKIE SAME jak Wariant A Checkpoint #4

---

## Faza 5: QA & Deployment (Dni 8-10, 2.5h) - WARIANT B

### Scope Prac

#### J. Testing (1.5h)

**Tasks:**
- **REUSE 36 existing tests** (UserInvoiceProfile, ValidNIP, UI booking wizard) ✅
- **Only NEW tests** (8 tests):
  - Invoice generation (3 tests)
  - PDF download (3 tests)
  - Email send (2 tests)

**Files Created:**
- `tests/Feature/InvoiceGenerationTest.php` (8 tests, not 12)
- `tests/Unit/InvoiceNumberGeneratorTest.php` (5 tests)
- `tests/Feature/InvoiceAuthorizationTest.php` (6 tests)

**Total tests:** 36 existing + 8 new + 5 unit + 6 policy = **55 tests** (higher coverage than Wariant A!)

**Deliverable:**
✅ 55 tests pass (98% coverage)

**Oszczędność vs Wariant A:** 1.5h (reuse 36 existing tests)

---

#### K. Documentation (1h)

**Tasks:**
- TAKIE SAME jak Wariant A (README + Installation + User Guide + CLAUDE.md)

**Files Created:**
- 3 docs

**Deliverable:**
✅ Complete documentation

**Oszczędność vs Wariant A:** 1h (existing docs structure)

---

### Final Checkpoint (Dzień 10) - WARIANT B

**Acceptance Criteria:**
- TAKIE SAME jak Wariant A Final Checkpoint

**Deployment to Production:**
- TAKIE SAME procedury

---

## Porównanie Harmonogramów

| Aspekt | Wariant A (Od zera) | Wariant B (Z reuse) |
|--------|---------------------|---------------------|
| **Dni robocze** | 12-14 dni | 10 dni |
| **Godziny/dzień** | ~4h avg | ~3h avg |
| **Total effort** | 45-50h | 30h |
| **Checkpointy** | 5 checkpointów | 5 checkpointów |
| **Timeline** | 2 tygodnie | 1.5 tygodnia |
| **Testy** | 23 nowe testy | 8 nowych + 36 existing = 44 total |
| **Test coverage** | 95% | 98% |
| **Risk** | Niskie (pełna kontrola) | Bardzo niskie (reuse tested code) |
| **Oszczędność** | Baseline | **-15h (1,500-2,000 PLN)** |

---

## Risk Management Per Faza

**Oba warianty:**

### Faza 1 (Foundation/Merge)
**Risk:** Settings validation issues
**Mitigation:** Reuse ValidNIP rule
**Contingency:** +0.5h

### Faza 2 (PDF Engine)
**Risk:** Polskie znaki jako "?"
**Mitigation:** DejaVu Sans font, early testing
**Contingency:** +1h

### Faza 3 (Filament)
**Risk:** Filament v4 namespace issues
**Mitigation:** Follow CLAUDE.md, existing patterns
**Contingency:** +0.5h

### Faza 4 (Email)
**Risk:** Queue job failures (Redis down)
**Mitigation:** Graceful error handling, fallback sync email
**Contingency:** +0.5h

### Faza 5 (QA)
**Risk:** Test failures na staging
**Mitigation:** Early testing per faza
**Contingency:** +1h

**Total Contingency:** 3.5h (included in buffer)

---

## Daily Standup Protocol (Oba Warianty)

### Format (5 minut via Slack/email)

**Dzień X - Status Update:**
- **Yesterday:** [co zostało zrobione]
- **Today:** [co będzie robione]
- **Blockers:** [jeśli jakieś problemy]
- **ETA:** [on track / delayed / ahead]

**Example:**
```
Dzień 3 - Status Update (Wariant B):
- Yesterday: Merge verified (36 tests green), Settings complete
- Today: Invoice models + migrations implementation
- Blockers: None
- ETA: On track (Checkpoint #1 passed ✅)
```

---

## Podsumowanie

**WARIANT A (Od Zera):**
- ✅ Pełna niezależność (zero dependencies)
- ✅ Kompletna implementacja od podstaw
- ✅ 12-14 dni roboczych
- ✅ 45-50h effort
- ✅ 23 testy (95% coverage)

**WARIANT B (Z Reuse):**
- ✅ Oszczędność 1,500-2,000 PLN vs Wariant A
- ✅ Szybsza realizacja (10 dni)
- ✅ 30h effort
- ✅ 55 testów (98% coverage - WIĘCEJ niż A!)
- ✅ Mniejsze ryzyko (reuse tested code)
- ⚠️ Wymaga merge przed startem

**Rekomendacja:**
- **Wariant B** jeśli klient jest pewien merge
- **Wariant A** jeśli klient woli niezależność i brak decyzji merge teraz

---

**Prepared by:** Commercial Estimate Specialist
**Date:** 24 grudnia 2024
**Version:** 2.0 (2 warianty)
**Branch:** feature/invoice-pdf-estimation
