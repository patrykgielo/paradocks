# Szczegółowa Wycena: System Generowania Faktur PDF

**Data:** 24 grudnia 2024
**Klient:** ParaDocks Car Detailing
**Wykonawca:** Senior Laravel Developer
**Stawka bazowa:** 100 PLN/h
**Projekt:** Kompletny system generowania faktur VAT w formacie PDF

---

## 1. Streszczenie Wykonawcze

### Problem

Obecnie ParaDocks zbiera dane fakturowe od klientów podczas rezerwacji (checkbox "Potrzebuję faktury", formularz NIP/firma/adres), ale faktury muszą być wystawiane ręcznie. Proces ten:
- Zajmuje ~25 minut na fakturę
- Wymaga ręcznego przepisywania danych
- Generuje ryzyko błędów (złe obliczenia VAT, literówki w NIP)

### Rozwiązanie

Automatyczny system generowania faktur VAT, który:
- Generuje profesjonalne faktury PDF jednym kliknięciem
- Automatycznie numeruje faktury (FV/2025/12/0001, sekwencyjnie)
- Oblicza VAT 23% (brutto → netto)
- Integruje się z panelem admina (Filament) i profilem klienta
- Wysyła faktury emailem z PDF załącznikiem

### Korzyści

**Oszczędność czasu:**
- **Przed:** 25 minut ręcznego wystawiania faktury
- **Po:** 30 sekund (jeden klik)
- **Redukcja:** 95% czasu

**Eliminacja błędów:**
- Automatyczna walidacja NIP (polski format, checksum)
- Automatyczne obliczenia VAT
- Brak błędów przepisywania danych

**Profesjonalizm:**
- Spójny wygląd faktur
- Zgodność z polskimi przepisami (Art. 106e VAT)
- Logo firmy na fakturze

### Dwa Warianty Implementacji

Przygotowałem **DWA warianty** implementacji - w zależności od tego, czy wykorzystamy kod już napisany w poprzedniej fazie projektu, czy zrobimy wszystko od zera.

---

## 2. Dwa Warianty Implementacji

### 🎯 WARIANT A: Implementacja "Od Zera" (POLECAM)

**Założenie:** NIE zakładamy wykorzystania żadnego wcześniejszego kodu (feature/invoice-system-with-estimate-agent).

**Zakres pełny:**
- **UserInvoiceProfile model** (zbieranie danych NIP, company_name, address)
- **UI w booking wizard** (checkbox "Potrzebuję faktury", formularz z walidacją)
- **ValidNIP rule** (checksum mod 11, polski format)
- **Invoice + InvoiceItem models** (immutable snapshots cen)
- **InvoiceNumberGenerator** (Redis lock, FV/YYYY/MM/XXXX)
- **InvoicePdfGenerator** (DomPDF + Tailwind CSS inline)
- **Settings system** dla ParaDocks (nazwa firmy, NIP, REGON, logo, konto)
- **Filament InvoiceResource** (CRUD + actions: generate/download/email)
- **Email notification** z PDF attachment (queue-based)
- **Storage** (invoices w bazie danych, PDF on-the-fly)
- **Pełne testy** (35-40 testów: feature + unit + policy)

**Czas:** 45-50 godzin roboczych (12-14 dni roboczych @ 4h/dzień)

**Cennik:**

| Opcja | Stawka | Koszt Netto | Koszt Brutto (VAT 23%) |
|-------|--------|-------------|------------------------|
| **Standard** | 100 PLN/h | **4,500-5,000 PLN** | **5,535-6,150 PLN** |
| **Premium** | 120 PLN/h | **5,400-6,000 PLN** | **6,642-7,380 PLN** |

**Dlaczego polecam ten wariant?**
- ✅ Żadnych zależności od wcześniejszych decyzji
- ✅ Pewny rezultat
- ✅ Kompletny system z gwarancją działania
- ✅ Nie trzeba decydować o merge teraz

---

### 💡 WARIANT B: Wykorzystanie Wcześniejszego Kodu (Opcjonalny)

**Założenie:** Klient ZDECYDUJE SIĘ zmergować `feature/invoice-system-with-estimate-agent` PRZED rozpoczęciem prac nad PDF.

**Co JUŻ JEST zrobione (jeśli merge):**
- ✅ UserInvoiceProfile model + migracja (4h oszczędności)
- ✅ UI w booking wizard (checkbox + formularz NIP/firma/adres) (3h oszczędności)
- ✅ ValidNIP rule (checksum mod 11) (2h oszczędności)
- ✅ Snapshot invoice_* w appointments (1h oszczędności)
- ✅ 36 testów zapewniających jakość (2h oszczędności)
- **Łączna oszczędność: 12 godzin**

**Co TRZEBA dodać:**
- Settings system dla ParaDocks (dane firmy, logo)
- Invoice + InvoiceItem models
- InvoiceNumberGenerator (Redis lock)
- InvoicePdfGenerator (DomPDF + Tailwind template)
- Filament InvoiceResource (CRUD + actions)
- Email notification z PDF
- Storage
- Rozszerzenie testów (8 nowych testów)

**Czas:** 30 godzin roboczych (10 dni roboczych @ 3h/dzień)

**Cennik:**

| Opcja | Stawka | Koszt Netto | Koszt Brutto (VAT 23%) |
|-------|--------|-------------|------------------------|
| **Z Rabatem** | 85 PLN/h | **2,550 PLN** | **3,137 PLN** ⭐ |
| **Standard** | 100 PLN/h | **3,000 PLN** | **3,690 PLN** |

**Oszczędność:** 1,500-2,000 PLN vs Wariant A

**Dlaczego tańsze?**
- Wykorzystujemy 12 godzin gotowego kodu
- Mniejsze ryzyko błędów (kod już przetestowany w 36 testach)
- Szybsza realizacja

**WAŻNE:** Wymaga decyzji o merge PRZED rozpoczęciem. Jeśli klient nie zdecyduje się na merge - automatycznie Wariant A.

---

### 🤔 Który wariant wybrać?

**Wybierz WARIANT A jeśli:**
- ✅ Nie chcesz mergować wcześniejszego kodu
- ✅ Wolisz mieć wszystko zrobione "na świeżo"
- ✅ Nie zależy Ci na czasie (12-14 dni vs 10 dni)
- ✅ Chcesz uniknąć decyzji o merge teraz

**Wybierz WARIANT B jeśli:**
- ✅ Zgadzasz się na merge wcześniejszego kodu do systemu
- ✅ Chcesz zaoszczędzić 1,500-2,000 PLN
- ✅ Zależy Ci na szybszej realizacji (10 dni)
- ✅ Jesteś pewien, że wcześniejszy kod jest OK

**WAŻNE:** Nie musisz decydować o merge teraz! Możesz to zrobić później, przed rozpoczęciem prac. Jeśli zdecydujesz się na Wariant A, zawsze możemy przejść na Wariant B później (ale nie odwrotnie).

---

## 3. Zakres Prac - WARIANT A (Od Zera, 45-50h)

### ETAP 1: Zbieranie Danych Firmowych + Fundament (14 godzin)

#### A. UserInvoiceProfile Model + UI w Booking Wizard (6h)

**Deliverables:**
- Model `UserInvoiceProfile` z relacją do `User` (hasOne)
- Migracja z polami:
  - `nip` VARCHAR(10) UNIQUE
  - `company_name` VARCHAR(255)
  - `address_street` VARCHAR(255)
  - `address_city` VARCHAR(100)
  - `address_postal_code` VARCHAR(10)
- UI w booking wizard (Step 4 - Contact Info):
  - Checkbox "Potrzebuję faktury" (Alpine.js reactivity)
  - Conditional form (pokazuje się po zaznaczeniu)
  - Pola: NIP, Nazwa firmy, Adres (ulica, miasto, kod pocztowy)
  - Frontend validation (NIP format: 10 cyfr)
- Snapshot `invoice_*` w tabeli `appointments`:
  - `invoice_requested` BOOLEAN
  - `invoice_nip` VARCHAR(10)
  - `invoice_company_name` VARCHAR(255)
  - `invoice_address` TEXT

**Techniczne detale:**
- Alpine.js dla reactivity (existing pattern w booking wizard)
- Livewire validation messages (PL)
- Snapshot pattern: dane faktury immutable (nie zmienia się po zapisie)

**Szczegółowy breakdown:**
- Migration `UserInvoiceProfile` + model definition: 1.5h
- UI w booking wizard (checkbox + conditional form): 3h
  - Blade template update (Step 4)
  - Alpine.js reactivity
  - Frontend validation (NIP format)
- Snapshot logic w `appointments`: 1.5h
  - Migration add invoice_* columns
  - BookingController update (save invoice data)
  - Tests

#### B. ValidNIP Rule (2h)

**Deliverables:**
- Custom validation rule `App\Rules\ValidNIP`
- Checksum mod 11 algorithm (polski NIP validation)
- Error messages (PL + EN):
  - "NIP musi mieć 10 cyfr"
  - "Nieprawidłowy NIP (błędna suma kontrolna)"
- Unit tests (10 scenarios):
  - ✅ Valid NIP: `1234567890`
  - ✅ Valid NIP with checksum
  - ❌ Invalid: too short
  - ❌ Invalid: too long
  - ❌ Invalid: contains letters
  - ❌ Invalid: wrong checksum
  - Edge cases: null, empty string, whitespace

**Szczegółowy breakdown:**
- ValidNIP rule implementation (checksum algorithm): 1h
- Error messages (PL + EN lang files): 0.5h
- Unit tests (10 scenarios): 0.5h

#### C. Settings System (3h)

**Deliverables:**
- Settings tab "Dane firmy" w `/admin/system-settings`
- Filament form z polami:
  - Nazwa firmy (required)
  - NIP (ValidNIP rule, required)
  - REGON (optional)
  - Adres: ulica, numer, kod pocztowy, miasto (required)
  - Numer konta bankowego (IBAN, required)
  - Logo firmy (FileUpload, PNG/JPG, max 2MB)
- Settings keys w `system_settings` table:
  - `invoice.company_name`
  - `invoice.company_nip`
  - `invoice.company_regon`
  - `invoice.company_address`
  - `invoice.company_bank_account`
  - `invoice.company_logo` (path)

**Techniczne detale:**
- Wykorzystanie istniejącego Settings systemu (tabela `system_settings`)
- Filament `Section` + `FileUpload` dla logo
- Validation: ValidNIP rule (reuse), IBAN format

**Szczegółowy breakdown:**
- Filament Settings page (formularz): 1.5h
- FileUpload dla logo + preview: 1h
- Validation + zapis: 0.5h

#### D. Invoice Models + Database (3h)

**Deliverables:**
- Model `Invoice` z polami:
  - `number` VARCHAR(20) UNIQUE (FV/2025/12/0001)
  - `issue_date`, `sale_date` (DATE)
  - `appointment_id` FK (belongsTo Appointment)
  - `customer_id` FK (belongsTo User)
  - Seller data (snapshot z Settings):
    - `seller_name`, `seller_nip`, `seller_regon`
    - `seller_address`, `seller_bank_account`
  - Buyer data (snapshot z UserInvoiceProfile):
    - `buyer_name`, `buyer_nip`, `buyer_address`
  - Totals:
    - `total_net` DECIMAL(10,2)
    - `total_vat` DECIMAL(10,2)
    - `total_gross` DECIMAL(10,2)
  - `timestamps`, `softDeletes`
- Model `InvoiceItem` (pozycje faktury):
  - `invoice_id` FK
  - `name` VARCHAR(255) (nazwa usługi)
  - `quantity` INT DEFAULT 1
  - `unit_price_net` DECIMAL(10,2)
  - `vat_rate` INT DEFAULT 23
  - `total_net`, `total_vat`, `total_gross` DECIMAL(10,2)
- 2 migracje bazy danych
- Relacje:
  - `Invoice` hasMany `InvoiceItems`
  - `Appointment` hasOne `Invoice`
  - `Invoice` belongsTo `User` (customer)
- Factories dla testów

**Techniczne detale:**
- Snapshot pattern: seller/buyer data immutable (zmiany w Settings nie wpływają na stare faktury)
- Soft deletes: faktury nie usuwane fizycznie (wymóg księgowy)
- Decimal precision (10,2) dla kwot

**Szczegółowy breakdown:**
- Invoice model + migration: 1h
- InvoiceItem model + migration: 0.5h
- Relacje (Appointment, User): 0.5h
- Factories + seeders: 1h

**Deliverables ETAP 1:**
- ✅ UserInvoiceProfile model
- ✅ UI w booking wizard (checkbox + formularz)
- ✅ ValidNIP rule
- ✅ Settings "Dane firmy"
- ✅ Invoice + InvoiceItem models
- ✅ Wszystkie migracje + factories

---

### ETAP 2: PDF Generation (16 godzin)

#### E. InvoiceNumberGenerator Service (3h)

**Deliverables:**
- Service `App\Services\InvoiceNumberGenerator`
- Generowanie sekwencyjnych numerów faktur:
  - Format: `FV/YYYY/MM/XXXX` (FV/2025/12/0001, FV/2025/12/0002, ...)
  - Sekwencja resetuje się co miesiąc (styczeń: 0001, luty: 0001)
- Redis distributed locking (zapobiega duplikatom):
  - `Cache::lock('invoice-number-generation', 10)` (10s timeout)
  - Atomic query: `SELECT MAX(number) WHERE YEAR(created_at) = X AND MONTH(created_at) = Y`
  - Increment + 1, pad to 4 digits
- Rollback mechanizm (w razie błędu transaction rollback)
- Unit tests (5 scenarios):
  - ✅ Format poprawny (FV/2025/12/0001)
  - ✅ Sekwencja (0001, 0002, 0003)
  - ✅ Reset per miesiąc
  - ✅ Konkurencyjność (2 procesy jednocześnie, brak duplikatów)
  - ❌ Redis timeout (exception)

**Techniczne detale:**
- Pessimistic locking w database (SELECT FOR UPDATE)
- Redis lock jako dodatkowa warstwa (distributed lock)
- Integration test symulujący konkurencję (multi-process)

**Szczegółowy breakdown:**
- Logika generowania numerów (query + format): 1h
- Redis locking implementation (Cache::lock): 1h
- Unit tests + konkurencyjność test: 1h

#### F. InvoicePdfGenerator Service (10h)

**Deliverables:**
- Service `App\Services\InvoicePdfGenerator`
- Method `generate(Invoice $invoice): string` (returns PDF binary)
- Blade template `resources/views/pdf/invoice.blade.php`:
  - **Header:**
    - Logo firmy (left, 150px width)
    - Dane sprzedawcy (right): Nazwa, NIP, REGON, Adres
  - **Title:**
    - "FAKTURA VAT"
    - Numer: FV/2025/12/0001
    - Data wystawienia, Data sprzedaży
  - **Nabywca:**
    - Dane klienta z invoice.buyer_*
  - **Tabela usług:**
    - Kolumny: Lp., Nazwa, Ilość, Cena netto, VAT%, Kwota VAT, Cena brutto
    - Każda pozycja faktury (InvoiceItem)
  - **Podsumowanie:**
    - Suma netto
    - Suma VAT (23%)
    - **DO ZAPŁATY** (bold, duża czcionka)
  - **Footer:**
    - Numer konta bankowego
    - Termin płatności (7 dni od daty wystawienia)
    - Podpis (placeholder)
- DomPDF konfiguracja:
  - Font: DejaVu Sans (polskie znaki: ą, ę, ć, ł, ń, ó, ś, ź, ż)
  - Paper: A4, portrait
  - Encoding: UTF-8
- Tailwind CSS inline (DomPDF nie wspiera external CSS):
  - Table-based layout (DomPDF nie wspiera flexbox/grid)
  - Inline styles (border, padding, font-size)
- VAT calculations:
  - Netto = Brutto / 1.23
  - VAT = Brutto - Netto
  - Formatting: "1 234,56 zł" (spacja separator, przecinek dziesiętny)

**Techniczne detale:**
- Composer dependency: `barryvdh/laravel-dompdf`
- Art. 106e VAT compliance (wszystkie wymagane pola):
  - NIP sprzedawcy, NIP nabywcy
  - Data wystawienia, Data sprzedaży
  - Numer sekwencyjny
  - Pozycje z VAT
  - Suma netto, VAT, brutto
- Polish number formatting helper: `number_format($amount, 2, ',', ' ')`

**Szczegółowy breakdown:**
- Composer install barryvdh/laravel-dompdf + config: 1h
- Blade template design (HTML + table layout): 4h
  - Header + logo
  - Tabela usług
  - Footer
- Tailwind CSS inline (DomPDF compatibility): 2h
- VAT calculations + data mapping: 1.5h
- Polish number formatting + DejaVu Sans font: 1h
- Testing (polskie znaki, layout, kalkulacje): 0.5h

#### G. Storage + Download (3h)

**Deliverables:**
- PDF **NIE** zapisywany na dysku (generowany on-the-fly)
- Metadata zapisana w bazie danych (tabela `invoices`)
- Controller `InvoiceController`:
  - `GET /appointments/{appointment}/invoice/download`
  - Authorization (AppointmentPolicy::downloadInvoice)
  - Response: PDF streaming (Content-Disposition: attachment)
- Rate limiting: 10 downloads/min per IP (throttle:invoice)
- Middleware: `auth`, `throttle:invoice`

**Techniczne detale:**
- PDF generowany on-demand (oszczędność storage space)
- Zaleta: zmiana logo/danych firmy → można regenerować stare faktury
- Response headers:
  - `Content-Type: application/pdf`
  - `Content-Disposition: attachment; filename="FV-2025-12-0001.pdf"`

**Szczegółowy breakdown:**
- InvoiceController + route: 1h
- Authorization policy (owner/admin/staff): 1h
- Rate limiting + middleware: 0.5h
- Testing (download, authorization, rate limit): 0.5h

**Deliverables ETAP 2:**
- ✅ InvoiceNumberGenerator (Redis lock)
- ✅ InvoicePdfGenerator (DomPDF + Blade template)
- ✅ PDF download endpoint z authorization
- ✅ Rate limiting
- ✅ Polskie znaki wyświetlane poprawnie

---

### ETAP 3: Filament Admin Panel + UI (8 godzin)

#### H. InvoiceResource (4h)

**Deliverables:**
- Filament Resource `App\Filament\Resources\InvoiceResource`
- **ListInvoices** (index page):
  - Kolumny: Number, Customer, Date, Total, Status
  - Filters:
    - Zakres dat (date range picker)
    - Customer (search by name/email)
    - Status (future: paid/unpaid)
  - Sort: newest first (created_at DESC)
  - Pagination: 25 per page
- **ViewInvoice** (view page, read-only):
  - Infolists (Filament v4):
    - Sekcja "Dane faktury": Number, Issue Date, Sale Date
    - Sekcja "Nabywca": Name, NIP, Address
    - Sekcja "Sprzedawca": Name, NIP, REGON, Address
    - Sekcja "Pozycje": Table (InvoiceItems)
    - Sekcja "Podsumowanie": Net, VAT, **Gross** (bold)
  - Actions (header actions):
    - "Pobierz PDF" (download icon, green)
    - "Wyślij email" (mail icon, blue)
    - "Regeneruj PDF" (refresh icon, gray) - future use
  - Breadcrumbs: Invoices > FV/2025/12/0001
- Authorization:
  - Admin: wszystkie faktury
  - Staff: tylko faktury z przypisanych rezerwacji
  - Customer: nie ma dostępu do `/admin/invoices` (tylko własne z profilu)

**Techniczne detale:**
- Filament v4 namespaces:
  - `Filament\Schemas\Components\*` (Section, Grid)
  - `Filament\Infolists\Components\*` (TextEntry, IconEntry)
- Eager loading: `->with(['customer', 'appointment', 'items'])`
- Formatted totals: `->money('PLN', locale: 'pl_PL')`

**Szczegółowy breakdown:**
- ListInvoices + filters: 1.5h
- ViewInvoice + Infolists: 2h
- Actions (Download/Email/Regenerate): 0.5h

#### I. AppointmentResource Integration (2h)

**Deliverables:**
- Header action "Wygeneruj fakturę" w `ViewAppointment`
- Walidacja przed generowaniem:
  - ✅ `invoice_requested = true` (klient zaznaczył checkbox)
  - ✅ `service_id != null` (rezerwacja ma usługę)
  - ✅ Wszystkie dane nabywcy wypełnione (NIP, nazwa firmy, adres)
- Action logic:
  - Create Invoice record (InvoiceNumberGenerator)
  - Create InvoiceItem (z appointment.service)
  - Redirect do ViewInvoice
  - Toast notification: "Faktura wygenerowana: FV/2025/12/0001"
- Conditional display (przycisk widoczny tylko jeśli `invoice_requested=true`)
- Disable jeśli faktura już istnieje (appointment->invoice != null)

**Techniczne detale:**
- Filament HeaderAction (zielony przycisk w header)
- Validation errors: toast notification (red)
- Success: redirect + green toast

**Szczegółowy breakdown:**
- Header action + walidacja: 1h
- Action logic (generate invoice): 0.5h
- Conditional display + testing: 0.5h

#### J. Customer Panel Integration (2h)

**Deliverables:**
- Przycisk "Pobierz fakturę" w `/profile/appointments` (customer panel)
- Conditional display:
  - ✅ `invoice_requested = true`
  - ✅ `appointment->invoice != null` (faktura wygenerowana)
- Link: `href="{{ route('appointment.invoice.download', $appointment) }}"`
- Target: `_blank` (otwiera w nowej karcie)
- Icon: document download (Heroicon)
- Blade template update:
  - Sekcja "Szczegóły rezerwacji" → dodać wiersz "Faktura"
  - Conditional `@if($appointment->invoice)`

**Techniczne detale:**
- Route authorization: AppointmentPolicy::downloadInvoice
- Middleware: auth (guest redirect to login)
- Testing: download jako customer (owner), download jako guest (403)

**Szczegółowy breakdown:**
- Blade template update (przycisk): 1h
- Conditional rendering: 0.5h
- Testing (authorization, download): 0.5h

**Deliverables ETAP 3:**
- ✅ InvoiceResource (List + View + Actions)
- ✅ AppointmentResource integration ("Wygeneruj fakturę")
- ✅ Customer panel integration ("Pobierz fakturę")
- ✅ Authorization working (admin/staff/customer)

---

### ETAP 4: Email + Automation (5 godzin)

#### K. Email Notification (3h)

**Deliverables:**
- Mailable `App\Mail\InvoiceGenerated`
- Queue job `App\Jobs\SendInvoiceEmailJob` (async sending)
- Blade email template `resources/views/emails/invoice-generated-{pl|en}.blade.php`:
  - Subject (PL): "Twoja faktura FV/2025/12/0001"
  - Subject (EN): "Your invoice FV/2025/12/0001"
  - Body:
    - Podziękowanie za rezerwację
    - Informacja o fakturze (numer, kwota)
    - Link do pobrania: `{{ route('appointment.invoice.download', $appointment) }}`
    - **Załącznik PDF** (generated on-the-fly)
  - Footer: Logo ParaDocks, dane kontaktowe
- Action "Wyślij email" w InvoiceResource (header action)
- Email log w tabeli `email_sends` (existing feature - reuse)

**Techniczne detale:**
- Queue: Redis (already configured)
- Attachment: `->attach($pdfBinary, 'faktura.pdf', ['mime' => 'application/pdf'])`
- Email service: SMTP Gmail App Password (reuse existing config)
- Job retries: 3 attempts (Laravel queue default)

**Szczegółowy breakdown:**
- Mailable + queue job: 1h
- Email template (PL + EN): 1h
- Action w InvoiceResource: 0.5h
- Testing (email send, attachment): 0.5h

#### L. Automation (Optional Future, 2h)

**Deliverables (future enhancement - NIE w MVP):**
- Event listener `InvoiceGenerated` event
- Auto-send email po wygenerowaniu faktury (optional)
- Scheduled task: reminder email jeśli faktura nie zapłacona po 7 dniach

**Dla Wariantu A - POMINIĘTE** (można dodać w przyszłości za 2h)

**Deliverables ETAP 4:**
- ✅ Email z PDF załącznikiem
- ✅ Queue job (async sending)
- ✅ Action "Wyślij email" w Filament
- ✅ Email templates (PL + EN)

---

### ETAP 5: Testing + Documentation + Polish (6 godzin)

#### M. Testing (3h)

**Deliverables:**
- **Feature tests** (InvoiceGenerationTest) - 12 cases:
  - ✅ Admin can generate invoice from appointment
  - ✅ Customer cannot generate invoice (only admin)
  - ✅ Generated invoice has correct number format (FV/YYYY/MM/XXXX)
  - ✅ Invoice totals calculated correctly (net + VAT = gross)
  - ✅ PDF download requires authentication
  - ✅ Customer can download own invoice
  - ✅ Customer cannot download other's invoice (403)
  - ✅ Staff can download invoice from assigned appointment
  - ✅ Staff cannot download invoice from not assigned appointment (403)
  - ✅ Rate limiting works (11th request = 429)
  - ✅ PDF has correct Content-Type header
  - ✅ PDF contains invoice number (assertion on binary content)
- **Unit tests** (InvoiceNumberGeneratorTest) - 5 cases:
  - ✅ Format: FV/YYYY/MM/XXXX
  - ✅ Sequential: 0001, 0002, 0003
  - ✅ Reset per month (January = 0001, February = 0001)
  - ✅ Concurrent generation (2 processes, no duplicates)
  - ❌ Redis timeout (exception thrown)
- **Policy tests** (InvoiceDownloadAuthorizationTest) - 6 cases:
  - ✅ Owner can download
  - ✅ Admin can download any
  - ✅ Staff can download assigned
  - ❌ Staff cannot download not assigned
  - ❌ Guest cannot download (redirect to login)
  - ❌ Other customer cannot download

**Test helpers:**
- Factories: `Invoice::factory()`, `InvoiceItem::factory()`
- Assertions: `assertDatabaseHas`, `assertSee`, `assertStatus(200)`
- PDF assertions: `assertStringContainsString($pdf, 'FV/2025/12/0001')`

**Szczegółowy breakdown:**
- Feature tests (12 cases): 1.5h
- Unit tests (5 cases): 0.5h
- Policy tests (6 cases): 1h

**Target:** 95% test coverage (23 tests total)

#### N. Documentation (2h)

**Deliverables:**
- **README** w `docs/features/invoice-pdf-generation/README.md`:
  - Feature overview
  - Business benefits (oszczędność czasu, eliminacja błędów)
  - Quick start guide
  - FAQ
- **Installation Guide** w `docs/features/invoice-pdf-generation/INSTALLATION.md`:
  - Composer dependencies: `composer require barryvdh/laravel-dompdf`
  - Migrations: `php artisan migrate`
  - Seeders: `php artisan db:seed --class=InvoiceSettingSeeder`
  - Konfiguracja Settings (dane firmy, logo)
  - Deployment checklist
- **User Guide** w `docs/features/invoice-pdf-generation/USER_GUIDE.md`:
  - Jak wygenerować fakturę (admin panel)
  - Jak wysłać email z fakturą
  - Jak edytować dane firmy (Settings)
  - Jak pobrać fakturę (customer panel)
- **ADR** (Architecture Decision Record) - jeśli potrzebny:
  - `docs/decisions/ADR-XXX-invoice-pdf-generation.md`
  - Decyzja: DomPDF vs Spatie PDF (wybór + uzasadnienie)
  - Decyzja: On-the-fly PDF vs Storage (wybór + uzasadnienie)
- **CLAUDE.md update**:
  - Dodać w sekcji "Feature Documentation":
    - Invoice PDF Generation
    - Link do README
  - Dodać w "Commands Reference":
    - `php artisan db:seed --class=InvoiceSettingSeeder`

**Szczegółowy breakdown:**
- README + Installation Guide: 1h
- User Guide: 0.5h
- ADR + CLAUDE.md update: 0.5h

#### O. Code Review + Deployment Prep (1h)

**Deliverables:**
- Self-review checklist:
  - ✅ PSR-12 coding standards (run `./vendor/bin/pint`)
  - ✅ No hardcoded strings (use `config/`, `lang/` files)
  - ✅ Security: no SQL injection (use Eloquent), XSS protection (Blade {{ }})
  - ✅ Performance: no N+1 queries (use `->with()`)
  - ✅ Error handling: try/catch, user-friendly messages
- Deployment checklist:
  - ✅ Migrations tested (rollback + re-run)
  - ✅ Seeds ready (InvoiceSettingSeeder)
  - ✅ .env variables documented (no new env vars needed)
  - ✅ All tests pass (23/23 green)
- Production readiness:
  - ✅ Logging: invoice generation events (`Log::info()`)
  - ✅ Rollback strategy: migration down() works
  - ✅ Error handling: graceful failures (toast notifications, not exceptions)

**Szczegółowy breakdown:**
- Code review (Pint + checklist): 0.5h
- Deployment testing (staging): 0.5h

**Deliverables ETAP 5:**
- ✅ 23 tests pass (95% coverage)
- ✅ Complete documentation (4 docs)
- ✅ Production-ready code
- ✅ Deployment checklist OK

---

## 4. Zakres Prac - WARIANT B (Z Reuse, 30h)

### ✅ Co JUŻ MAMY (0h - reuse)

**Z feature/invoice-system-with-estimate-agent (jeśli merge):**
- ✅ UserInvoiceProfile model + migration (4h oszczędności)
- ✅ UI w booking wizard (checkbox + formularz NIP/firma/adres) (3h oszczędności)
- ✅ ValidNIP rule (checksum mod 11) (2h oszczędności)
- ✅ Snapshot invoice_* w appointments (1h oszczędności)
- ✅ 36 testów zapewniających jakość (2h oszczędności)

**Łączna oszczędność: 12 godzin**

**WAŻNE:** Ten wariant wymaga **merge `feature/invoice-system-with-estimate-agent` do `develop` PRZED rozpoczęciem** prac nad PDF. Jeśli klient nie zdecyduje się na merge - automatycznie Wariant A.

---

### ETAP 1: Merge Verification + Settings (3 godziny)

#### A. Merge Verification (1h)

**Deliverables:**
- Pull `feature/invoice-system-with-estimate-agent` do `develop`
- Resolve conflicts (jeśli są)
- Run existing 36 tests (wszystkie muszą przejść ✅)
- Verify functionality:
  - UserInvoiceProfile model działa
  - ValidNIP rule waliduje poprawnie
  - UI w booking wizard wyświetla formularz
  - Snapshot invoice_* zapisuje dane

**Breakdown:**
- Git merge + conflict resolution: 0.5h
- Run tests + verify: 0.5h

#### B. Settings System dla Danych Firmy (2h)

**Deliverables:**
- Settings tab "Dane firmy" w `/admin/system-settings`
- Formularz Filament (TAKIE SAME pola jak Wariant A):
  - Nazwa firmy, NIP, REGON, Adres, Konto bankowe
  - Logo firmy (FileUpload)
- Settings keys w `system_settings`

**Techniczne detale:**
- Wykorzystanie istniejącej reguły ValidNIP (już przetestowana)
- Filament patterns (szybsza implementacja, bo known patterns)

**Breakdown:**
- Formularz Filament (faster with patterns): 1h
- Logo upload + validation: 1h

**Oszczędność vs Wariant A:** 1h (dzięki existing patterns)

---

### ETAP 2: Invoice Models + PDF Generation (14 godzin)

#### C. Invoice Models + Database (2.5h)

**Deliverables:**
- TAKIE SAME jak Wariant A (Invoice + InvoiceItem models)
- 2 migracje bazy danych
- Relacje, factories, seeders

**Breakdown:**
- Invoice model + migration: 0.5h (faster with existing patterns)
- InvoiceItem model + migration: 0.5h
- Relacje + factories: 1.5h

**Oszczędność vs Wariant A:** 0.5h (existing test patterns)

#### D. InvoiceNumberGenerator (3h)

**Deliverables:**
- TAKIE SAME jak Wariant A (Redis lock, FV/YYYY/MM/XXXX)
- Unit tests (5 scenarios)

**Breakdown:**
- IDENTYCZNE jak Wariant A: 3h (no reuse możliwy)

#### E. InvoicePdfGenerator (8.5h)

**Deliverables:**
- TAKIE SAME jak Wariant A (DomPDF + Blade template + polskie znaki)
- Service, Blade template, PDF download

**Breakdown:**
- Composer install + config: 0.5h (faster, know existing setup)
- Blade template design: 4h (same as A)
- Tailwind inline CSS: 2h (same as A)
- VAT calculations: 1h (same as A)
- Testing: 1h (same as A)

**Oszczędność vs Wariant A:** 0.5h (faster setup)

---

### ETAP 3: Filament Admin + UI (6 godzin)

#### F. InvoiceResource (3h)

**Deliverables:**
- TAKIE SAME jak Wariant A (List + View + Actions)

**Breakdown:**
- ListInvoices + filters: 1h (faster with existing Filament patterns)
- ViewInvoice + Infolists: 1.5h (faster)
- Actions: 0.5h

**Oszczędność vs Wariant A:** 1h (existing resource patterns)

#### G. AppointmentResource Integration (1.5h)

**Deliverables:**
- TAKIE SAME jak Wariant A (header action "Wygeneruj fakturę")

**Breakdown:**
- Header action + walidacja: 0.5h (existing action patterns)
- Action logic: 0.5h
- Testing: 0.5h

**Oszczędność vs Wariant A:** 0.5h

#### H. Customer Panel Integration (1.5h)

**Deliverables:**
- TAKIE SAME jak Wariant A (przycisk "Pobierz fakturę")

**Breakdown:**
- Blade template update: 0.5h (existing customer panel patterns)
- Conditional rendering: 0.5h
- Testing: 0.5h

**Oszczędność vs Wariant A:** 0.5h

---

### ETAP 4: Email + Automation (4 godziny)

#### I. Email Notification (2.5h)

**Deliverables:**
- TAKIE SAME jak Wariant A (Mailable + queue job + PDF attachment)

**Breakdown:**
- Mailable + queue job: 0.5h (existing email patterns)
- Email template: 1h (reuse email layout)
- Action w Filament: 0.5h
- Testing: 0.5h

**Oszczędność vs Wariant A:** 0.5h (existing email system patterns)

---

### ETAP 5: Testing + Documentation (2.5 godziny)

#### J. Testing (1.5h)

**Deliverables:**
- **Tylko NOWE testy** (8 testów):
  - Invoice generation tests (3 tests)
  - PDF download tests (3 tests)
  - Email send tests (2 tests)
- **REUSE existing 36 tests** (UserInvoiceProfile, ValidNIP, UI) - już działają ✅

**Breakdown:**
- Feature tests (8 nowych): 1h
- Policy tests: 0.5h

**Oszczędność vs Wariant A:** 1.5h (reuse 36 existing tests)

**Total tests:** 36 existing + 8 new = **44 tests** (higher coverage than Wariant A!)

#### K. Documentation (1h)

**Deliverables:**
- TAKIE SAME jak Wariant A (README + Installation + User Guide + CLAUDE.md)

**Breakdown:**
- README + Installation: 0.5h (faster with existing docs structure)
- User Guide + ADR: 0.5h

**Oszczędność vs Wariant A:** 1h (existing docs patterns)

---

## 5. Porównanie Wariantów

| Aspekt | Wariant A (Od zera) | Wariant B (Z reuse) |
|--------|---------------------|---------------------|
| **Czas** | 45-50h (12-14 dni) | 30h (10 dni) |
| **Koszt (standard)** | 4,500-5,000 PLN netto | 3,000 PLN netto |
| **Koszt (rabat)** | N/A | 2,550 PLN netto ⭐ |
| **Koszt brutto (standard)** | 5,535-6,150 PLN | 3,690 PLN |
| **Koszt brutto (rabat)** | N/A | 3,137 PLN ⭐ |
| **Zależności** | ZERO (niezależny) | Wymaga merge przed startem |
| **Ryzyko** | Niskie (pełna kontrola) | Bardzo niskie (reuse tested code) |
| **Testy** | 23 nowe testy | 8 nowych + 36 existing = 44 total |
| **Test coverage** | 95% | 98% (więcej testów) |
| **Timeline** | 12-14 dni roboczych | 10 dni roboczych |
| **Oszczędność** | Baseline | **1,500-2,000 PLN vs A** |
| **Merge decision** | NIE wymaga | TAK, przed startem |
| **Flexibility** | Można przejść A→B | Nie można B→A po merge |

### Kiedy który wariant?

**Rekomendacja WARIANT A jeśli:**
- Chcesz uniknąć decyzji o merge teraz
- Wolisz pełną niezależność (zero dependencies)
- Nie zależy Ci na oszczędności 1,500 PLN
- Wolisz "czysty start"

**Rekomendacja WARIANT B jeśli:**
- Jesteś pewien merge wcześniejszego kodu
- Chcesz zaoszczędzić 1,500-2,000 PLN
- Zależy Ci na szybszej realizacji (10 dni vs 12-14 dni)
- Większy test coverage (44 vs 23 testy) jest wartością

**WAŻNE:**
- Wariant A → Wariant B: **TAK** (jeśli klient zmerguje kod przed startem)
- Wariant B → Wariant A: **NIE** (po merge nie można "odmergować")

---

## 6. Harmonogram Implementacji

**Zobacz:** `harmonogram-5-faz.md` dla szczegółowych timelines obu wariantów.

**Krótkie porównanie:**

**WARIANT A:** 12-14 dni roboczych (4h/dzień avg)
- Tydzień 1: Fundament + PDF Engine (14h + 16h = 30h)
- Tydzień 2: Filament + Email + Testing (8h + 5h + 6h = 19h)
- **TOTAL:** 49h (rounded to 45-50h)

**WARIANT B:** 10 dni roboczych (3h/dzień avg)
- Tydzień 1: Merge + Settings + Invoice Models + PDF (3h + 14h = 17h)
- Tydzień 2: Filament + Email + Testing (6h + 4h + 2.5h = 12.5h)
- **TOTAL:** 29.5h (rounded to 30h)

**Milestone Checkpoints (oba warianty):**
- ✅ **Po dniu 2-4:** Settings working, Invoice models ready
- ✅ **Po dniu 4-6:** PDF generation working (demo klientowi)
- ✅ **Po dniu 7-9:** Kompletny system (final review)
- ✅ **Dzień 10-14:** Production deployment ready

---

## 7. Podsumowanie Czasowe

### WARIANT A: Od Zera

| Etap | Scope | Godziny |
|------|-------|---------|
| **1. Fundament** | UserInvoiceProfile + ValidNIP + Settings + Invoice Models | 14h |
| **2. PDF Generation** | Number Generator + PDF Generator + Storage | 16h |
| **3. Filament Admin + UI** | InvoiceResource + Appointment Integration + Customer Panel | 8h |
| **4. Email** | Mailable + Queue Job + Email Templates | 5h |
| **5. Testing + Docs** | 23 tests + Documentation + Code Review | 6h |
| **SUBTOTAL** | | **49h** |
| **Zaokrąglone** | Bufor wliczony w detale | **45-50h** |

**Koszt:**
- Standard (100 PLN/h): **4,500-5,000 PLN netto** (5,535-6,150 PLN brutto)
- Premium (120 PLN/h): **5,400-6,000 PLN netto** (6,642-7,380 PLN brutto)

---

### WARIANT B: Z Reuse

| Etap | Scope | Godziny |
|------|-------|---------|
| **0. Reuse** | UserInvoiceProfile + ValidNIP + UI + 36 testów | **(0h)** ✅ |
| **1. Merge + Settings** | Merge verification + Settings system | 3h |
| **2. Invoice Models + PDF** | Models + Number Generator + PDF Generator | 14h |
| **3. Filament Admin + UI** | InvoiceResource + Integrations | 6h |
| **4. Email** | Mailable + Queue Job | 4h |
| **5. Testing + Docs** | 8 nowych testów + Documentation | 2.5h |
| **SUBTOTAL** | | **29.5h** |
| **Zaokrąglone** | | **30h** |

**Koszt:**
- Z rabatem (85 PLN/h): **2,550 PLN netto** (3,137 PLN brutto) ⭐ REKOMENDACJA
- Standard (100 PLN/h): **3,000 PLN netto** (3,690 PLN brutto)

**Oszczędność vs Wariant A:** **1,500-2,000 PLN** (zależnie od opcji cenowej)

---

## 8. Wymagania Techniczne

**Backend:**
- PHP 8.2+ (już zainstalowane)
- Laravel 12 (już zainstalowane)
- MySQL 8.0 (już zainstalowane)
- Redis 7+ (już zainstalowane)

**Nowe Composer Packages:**
```bash
composer require barryvdh/laravel-dompdf
```

**Nowe NPM Packages:**
Brak (DomPDF nie wymaga Node.js)

**Environment Variables:**
```bash
# NO CHANGES NEEDED - wykorzystuje existing setup
MAIL_MAILER=smtp  # Już skonfigurowane
QUEUE_CONNECTION=redis  # Już skonfigurowane
```

**Deployment:**
- Docker Compose (istniejący setup)
- Migrations: `php artisan migrate`
- Seeders: `php artisan db:seed --class=InvoiceSettingSeeder`

---

## 9. Zarządzanie Ryzykiem

### High Risk: PDF Rendering Issues

**Problem:** Polskie znaki wyświetlają się jako "?" lub kropki
**Likelihood:** Medium (15%)
**Impact:** High (faktury nieczytelne)

**Mitigation:**
- DejaVu Sans font (built-in w DomPDF, pełne UTF-8)
- Early testing (dzień 4 - pokazać fakturę klientowi)
- Fallback: HTML invoice (bez PDF) - 1h effort

**Contingency Budget:** 1h

### Medium Risk: Numeracja Conflicts

**Problem:** Duplikaty numerów przy concurrent generation
**Likelihood:** Low (5%)
**Impact:** Medium (duplikaty)

**Mitigation:**
- Redis distributed locking (Cache::lock())
- Integration tests (multi-process)
- Manual correction script - 0.5h

**Contingency Budget:** 0.5h

### Low Risk: Settings Validation Issues

**Problem:** Admin wpisze błędny NIP firmy
**Likelihood:** Low (5%)
**Impact:** Low (trzeba poprawić w Settings)

**Mitigation:**
- ValidNIP rule (checksum mod 11)
- Filament validation
- Visual preview

**Contingency Budget:** 0.5h

**Total Contingency:** 2h (wliczony w bufor)

---

## 10. Deliverables Checklist

**Backend Components:**
- [ ] Model Invoice + migration
- [ ] Model InvoiceItem + migration
- [ ] InvoiceNumberGenerator service
- [ ] InvoicePdfGenerator service
- [ ] InvoiceController (download endpoint)
- [ ] AppointmentPolicy::downloadInvoice
- [ ] Mailable InvoiceGenerated + queue job
- [ ] Settings fields (company data)

**Wariant B dodatkowe (REUSE):**
- [✅] UserInvoiceProfile model (existing)
- [✅] ValidNIP rule (existing)
- [✅] UI w booking wizard (existing)
- [✅] 36 testów (existing)

**Frontend Components:**
- [ ] Filament InvoiceResource (List + View + Actions)
- [ ] ViewAppointment header action
- [ ] Settings tab "Dane firmy"
- [ ] Customer panel przycisk "Pobierz fakturę"
- [ ] Email template (PL/EN)
- [ ] PDF Blade template

**Testing:**
- [ ] Feature tests (Wariant A: 12, Wariant B: 8)
- [ ] Unit tests (5 cases)
- [ ] Policy tests (6 cases)
- [ ] Manual testing checklist

**Documentation:**
- [ ] README
- [ ] Installation Guide
- [ ] User Guide
- [ ] ADR (if needed)
- [ ] CLAUDE.md update

**Deployment:**
- [ ] Migrations tested
- [ ] Seeds ready
- [ ] All tests pass
- [ ] Production checklist OK

**Total Deliverables:**
- Wariant A: 30 items
- Wariant B: 34 items (30 + 4 existing verified)

---

## 11. Warunki Współpracy

### Forma Płatności

**WARIANT A:**

**Opcja 1: Całość z góry**
- Płatność: 4,500-5,000 PLN netto (5,535-6,150 PLN brutto) przed rozpoczęciem
- Bonus: Priorytetowe wsparcie 30 dni

**Opcja 2: Etapami (50% + 50%)**
- Płatność 1: 2,250-2,500 PLN netto przed rozpoczęciem
- Płatność 2: 2,250-2,500 PLN netto po dniu 7 (kompletny system)

**WARIANT B:**

**Opcja 1: Całość z góry (REKOMENDOWANA)**
- Płatność: 2,550 PLN netto (3,137 PLN brutto) przed rozpoczęciem
- Bonus: Priorytetowe wsparcie 30 dni

**Opcja 2: Etapami (50% + 50%)**
- Płatność 1: 1,275 PLN netto przed rozpoczęciem
- Płatność 2: 1,275 PLN netto po dniu 7 (kompletny system)

### Gwarancje

- **30 dni gwarancji:** Bezpłatne poprawki błędów
- **90 dni wsparcia:** Konsultacje techniczne email/chat (48h response)
- **Dokumentacja:** Kompletna instrukcja obsługi

### Wyłączenia (NIE wliczone)

**Dodatkowe koszty:**
- Modyfikacje szablonu faktury po akceptacji (50 PLN/h)
- Integracja z systemami księgowymi (wycena indywidualna)
- Faktury korygujące (~8h, osobna wycena)
- Custom branding (50 PLN/h)

---

## 12. Następne Kroki

### Dla Klienta (Decyzje)

1. **Wybór wariantu:**
   - [ ] **Wariant A: Od zera** (4,500-5,000 PLN / 12-14 dni)
   - [ ] **Wariant B: Z reuse** (2,550-3,000 PLN / 10 dni) ⭐

2. **Opcja cenowa:**
   - [ ] Standard (100 PLN/h)
   - [ ] Z rabatem (85 PLN/h) - tylko Wariant B ⭐
   - [ ] Premium (120 PLN/h)

3. **Forma płatności:**
   - [ ] Całość z góry (bonus: priorytet 30 dni)
   - [ ] Etapami (50% + 50%)

4. **Merge decision (tylko Wariant B):**
   - [ ] TAK - merge przed startem
   - [ ] NIE - zmiana na Wariant A

5. **Timeline:**
   - [ ] Start ASAP (po akceptacji + płatności)
   - [ ] Start: [DATA]

6. **Dane firmy:**
   - [ ] Dostarczę przed Dniem 1 (NIP, REGON, logo)
   - [ ] Dostarczę później (risk: delay)

---

## 13. Podsumowanie

### Rekomendacja

**WARIANT B: Z Rabatem (2,550 PLN netto / 3,137 PLN brutto)** ⭐

**Dlaczego?**
- ✅ Oszczędność 1,500-2,000 PLN vs Wariant A
- ✅ Szybsza realizacja (10 dni vs 12-14 dni)
- ✅ Wyższy test coverage (44 vs 23 testy)
- ✅ Mniejsze ryzyko (reuse przetestowanego kodu)
- ✅ Fair price (85 PLN/h poniżej market average)
- ✅ Win-win: klient oszczędza, developer ma kontynuację

**WARIANT A: Standard (4,500-5,000 PLN)** - jeśli:
- Wolisz niezależność (zero dependencies)
- Nie chcesz decydować o merge teraz
- "Czysty start" jest wartością

### Business Value (oba warianty)

**Oszczędność czasu:**
- 95% redukcja czasu wystawiania faktury (25 min → 30 sec)
- 20 faktur/miesiąc = **8.3h oszczędności miesięcznie**

**ROI:**
- Koszt: 2,550-5,000 PLN (one-time)
- Miesięczna oszczędność: 8.3h × 50 PLN/h = 415 PLN
- **Break-even: 6-12 miesięcy**

**Eliminacja błędów:**
- Zero błędów w obliczeniach VAT
- Zero literówek w NIP
- Zgodność z przepisami (Art. 106e VAT)

---

**Data ważności oferty:** 31 stycznia 2025
**Kontakt:** developer@paradocks.local
**Forma płatności:** Przelew tradycyjny / BLIK

---

*Dokument przygotowany przez: Senior Laravel Developer*
*Data: 24 grudnia 2024*
*Wersja: 2.0 (2 warianty)*
