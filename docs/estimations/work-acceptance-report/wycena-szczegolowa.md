# Wycena Szczegółowa - Raport Odbioru Prac

**Data:** 2025-12-22
**Projekt:** ParaDocks - System Rezerwacji Detailingu
**Funkcja:** Raport Odbioru Prac (Work Acceptance Report)
**Stawka:** 100 PLN/h netto
**Łączny Koszt:** 700 PLN netto (861 PLN brutto z VAT 23%)

---

## Podsumowanie Wykonawcze

**Problem Biznesowy:**
Brak profesjonalnego potwierdzenia odbioru prac po zakończonej wizycie. Detailerzy potrzebują:
- Dowodu wykonania usługi (ochrona przed sporami)
- Dokumentu do rozliczeń (faktura VAT wymaga podstawy)
- Potwierdzenia jakości prac (checkboxes)
- Warunków gwarancji (14 dni, zgodne z prawem konsumenckim)

**Rozwiązanie:**
System generowania PDF bezpośrednio z panelu admina. Jeden klik → gotowy raport do wydruku i podpisu.

**Wartość dla Klienta:**
- Oszczędność czasu: 15-20 min/wizytę → 5 sekund (automatyzacja)
- Profesjonalny wizerunek: Klient dostaje firmowy dokument, nie notatnik
- Ochrona prawna: Dowód wykonania + warunki gwarancji
- Szybsze rozliczenia: Podpis od razu → faktura bez opóźnień

**ROI:**
- 50 wizyt/miesiąc × 15 min oszczędności = 12.5h/miesiąc × 100 PLN = **1,250 PLN miesięczne oszczędności**
- Zwrot z inwestycji: **0.56 miesiąca** (17 dni!)
- Roczna wartość: 15,000 PLN w zaoszczędzonym czasie

---

## Szczegółowy Harmonogram Prac

**Metodologia wyceny:** Każde zadanie wymagające otwarcia edytora kodu = **pełna godzina rozpoczęta** (setup środowiska, context switching, dokumentacja kodu).

---

### Sesja 1: Setup & Backend Development (2h)
**Zadania:**
- Instalacja biblioteki dompdf (`composer require`)
- Publikacja konfiguracji (`vendor:publish`)
- Migracja bazy danych (company settings: name, NIP, logo)
- Service class `WorkAcceptanceReportService` (4 metody):
  - `generate()` - główna logika PDF
  - `getReportNumber()` - format RA-YYYY-MM-DD-{id}
  - `calculateFinancials()` - VAT 23% calculations
  - `getCompanyInfo()` - fetch z Settings
- Smoke test service (podstawowa weryfikacja że działa)

**Uzasadnienie czasu:**
- Install + setup (15 min faktycznie, ale uruchomienie środowiska)
- Migration (10 min + verify rollback)
- Service class (4 metody, relacje Eloquent, error handling)
- Unit tests podstawowe
- **Context switching między taskami = pełne 2h**

**Ryzyko:** Niskie (standardowe Laravel patterns)

---

### Sesja 2: PDF Template Development (2h)
**Zadania:**
- Szablon Blade `work-acceptance.blade.php` (10 sekcji):
  - Header firmowy (logo, NIP, adres)
  - Metadata raportu (numer, data)
  - Dane klienta (4 pola)
  - Dane pojazdu (VIN placeholder)
  - Tabela usług (nazwa, czas, cena)
  - Podsumowanie finansowe (netto, VAT 23%, brutto)
  - Lista kontrolna jakości (8 checkboxów)
  - Warunki gwarancji (14 dni)
  - Pola podpisów (3 sekcje)
  - Stopka RODO
- Inline CSS styling (dompdf requirement)
- DejaVu Sans font setup (polskie znaki)
- Layout A4 (210mm × 297mm, margins)
- **CSS iterations** (dompdf ma ograniczenia - wymaga testów)

**Uzasadnienie czasu:**
- 10 sekcji HTML (35 min faktycznie)
- CSS iterations (dompdf nie wspiera flexbox/grid → trzeba testować)
- Polish characters testing (ą,ć,ę,ł,ń,ó,ś,ź,ż)
- Page breaks, margins, print-friendly styling
- **Iteracje CSS = zawsze więcej niż myślisz = 2h**

**Ryzyko:** Średnie (dompdf CSS limitations, font encoding)

---

### Sesja 3: Integration & Comprehensive Testing (2h)
**Zadania:**
- Filament action w `AppointmentResource.php`:
  - Custom button "Generuj Raport Odbioru"
  - Visibility logic (tylko confirmed/completed)
  - Action handler (wywołanie service)
  - Error handling + Filament notifications
  - Download response (streamDownload)
- End-to-end testing (6 test cases):
  - Test 1: Happy path (completed appointment)
  - Test 2: Weryfikacja danych (customer, vehicle, services)
  - Test 3: Obliczenia VAT (accuracy check)
  - Test 4: Button visibility (status filtering)
  - Test 5: Polish characters rendering
  - Test 6: Fizyczny wydruk A4
- Edge case testing:
  - Brak vehicle data (fallback handling)
  - Długie teksty (text overflow)
  - Brak logo (graceful degradation)
- Bug fixes z testów

**Uzasadnienie czasu:**
- Filament action (20 min faktycznie)
- 6 test cases (każdy 5-10 min = ~1h)
- Edge cases debugging (zawsze znajduje się coś)
- **Testing nigdy nie zajmuje tyle ile myślisz = 2h**

**Ryzyko:** Średnie (edge cases, dompdf rendering issues)

---

### Sesja 4: Polish & Documentation (1h)
**Zadania:**
- Physical print test (verify A4, margins, readability)
- Final CSS iterations (jeśli wydruk pokazał problemy)
- Feature documentation:
  - README.md (business case, usage)
  - Technical docs (architecture, code examples)
  - User guide (instrukcja dla admina)
  - Changelog entry
- Code cleanup (remove debug code, comments)
- Final deployment verification

**Uzasadnienie czasu:**
- Print test + iterations (20-30 min)
- Documentation (4 pliki markdown)
- Final review
- **Ostatnia sesja = zawsze pełna godzina (polish & docs)**

**Ryzyko:** Niskie (finalizacja, bez nowego kodu)

---

## Podsumowanie Czasu

**Metodologia:** Pełne godziny rozpoczęte (każda sesja = context switching + setup + development + verification)

| Sesja | Zakres Prac | Czas (h) | Koszt (PLN) |
|-------|-------------|----------|-------------|
| **Sesja 1** | Setup & Backend Development | 2h | 200 |
| **Sesja 2** | PDF Template Development | 2h | 200 |
| **Sesja 3** | Integration & Comprehensive Testing | 2h | 200 |
| **Sesja 4** | Polish & Documentation | 1h | 100 |
| **RAZEM (Netto)** | **4 sesje pracy** | **7h** | **700** |
| **VAT 23%** | - | - | **161** |
| **DO ZAPŁATY** | - | - | **861** |

**Dlaczego pełne godziny?**
- Każde zadanie wymaga context switching (przełączenie myślenia)
- Setup środowiska (uruchomienie kontenerów, edytora, testów)
- Documentation in-code (komentarze, docblocks)
- Verification (sprawdzenie że działa poprawnie)

**Przykład:** "Migration (10 min)" faktycznie to:
- Uruchom środowisko (2 min)
- Napisz migration (10 min)
- Test migrate + rollback (5 min)
- Verify w bazie (3 min)
- **Total: 20 min, ale w ramach sesji 2h z innymi taskami**

---

## Ocena Ryzyka

### Niskie Ryzyko (80%)
- Instalacja biblioteki: Stabilna, sprawdzona
- Migracja: Additive, zero breaking changes
- Service class: Stateless design
- Filament integration: Udokumentowane API

### Średnie Ryzyko (20%)
- Szablon PDF: dompdf ma ograniczenia CSS
  - **Mitigacja:** Proste tabele, inline styles, DejaVu Sans
- Polskie znaki: Potencjalne problemy z encoding
  - **Mitigacja:** DejaVu Sans wspiera UTF-8

### Wysokie Ryzyko (0%)
- Brak krytycznych zależności zewnętrznych

---

## Warunki Płatności

**Model:** 50/50 (standard dla małych projektów)

- **Zaliczka:** 350 PLN netto (430.50 PLN brutto) przed rozpoczęciem
- **Finalizacja:** 350 PLN netto (430.50 PLN brutto) po uruchomieniu

**Termin realizacji:** 1 tydzień od wpłaty zaliczki

**Gwarancja:** 14 dni bugfixów (0 PLN)

---

## Podsumowanie

**Koszt:** 700 PLN netto (861 PLN brutto)
**Czas:** 7h w 4 sesjach pracy (1 tydzień delivery)
**ROI:** 0.56 miesiąca (17 dni zwrotu!)
**Ryzyko:** Niskie-Średnie (comprehensive testing included)
**Wartość roczna:** 15,000 PLN w zaoszczędzonym czasie

**Rekomendacja:** ✅ Zielone światło. Uczciwa wycena z pełnymi godzinami, konkretna wartość, realistyczny scope.
