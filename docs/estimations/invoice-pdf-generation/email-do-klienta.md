# Email do Klienta: Wycena Systemu Faktur PDF

**Do:** Właściciel ParaDocks
**Od:** Senior Laravel Developer
**Temat:** Oferta: Automatyczne Faktury PDF dla ParaDocks
**Data:** 24 grudnia 2024

---

Dzień dobry,

Przesyłam ofertę na system automatycznego generowania faktur VAT w aplikacji ParaDocks. Poniżej znajdą Państwo szczegóły dotyczące tego, co otrzymacie, ile to będzie kosztować i jak długo potrwa implementacja.

---

## Co Państwo otrzymacie?

### 1. Automatyczne Generowanie Faktur

Jeden przycisk "Wygeneruj fakturę" w panelu admina - i faktura VAT gotowa w 30 sekund. Bez ręcznego przepisywania danych, bez kalkulatora do obliczenia VAT.

**Co będzie na fakturze:**
- Logo Państwa firmy
- Wszystkie wymagane dane (NIP, REGON, adres, konto bankowe)
- Dane klienta (automatycznie z formularza rezerwacji)
- Szczegóły usługi (nazwa, cena netto, VAT 23%, cena brutto)
- Automatyczna numeracja (FV/2025/12/0001, FV/2025/12/0002, ...)

### 2. Panel do Edycji Danych Firmy

Nowa zakładka w panelu admina "Dane firmy", gdzie mogą Państwo edytować:
- Nazwę firmy
- NIP i REGON
- Adres
- Numer konta bankowego
- Logo (wystarczy wgrać plik)

Wszystkie te dane automatycznie pojawią się na każdej nowej fakturze.

### 3. Faktury dla Klientów

Przycisk "Pobierz fakturę" w profilu klienta - klient sam pobierze swoją fakturę bez konieczności dzwonienia czy wysyłania emaila. Oszczędza Państwa czas i zwiększa wygodę klientów.

### 4. Wysyłka Emailem (Opcjonalnie)

Jeśli chcą Państwo, faktura może być wysłana automatycznie emailem do klienta - z PDF w załączniku. Jeden klik w panelu admina i gotowe.

---

## Ile to będzie kosztować?

Przygotowałem **DWA warianty** implementacji - w zależności od tego, czy wykorzystamy kod już napisany w poprzedniej fazie projektu, czy zrobimy wszystko od zera.

### 🎯 WARIANT A: Implementacja "Od Zera" (POLECAM)

**Założenie:** Nie zakładamy wykorzystania żadnego wcześniejszego kodu. Kompletna implementacja od podstaw.

**Zakres:**
- Zbieranie danych do faktury w formularzu rezerwacji (NIP, nazwa firmy, adres)
- Walidacja NIP (polski format)
- Panel "Dane firmy" w adminie
- Generowanie PDF z fakturą
- Automatyczna numeracja
- Email z PDF
- Pełne testy

**Czas:** 45-50 godzin roboczych (12-14 dni)

**Cennik:**

| Opcja | Stawka | Koszt Netto | Koszt Brutto (VAT 23%) |
|-------|--------|-------------|------------------------|
| **Standard** | 100 PLN/h | **4,500-5,000 PLN** | **5,535-6,150 PLN** |
| **Premium** | 120 PLN/h | **5,400-6,000 PLN** | **6,642-7,380 PLN** |

**Dlaczego polecam ten wariant?**
- Nie zakłada niczego - pewny rezultat
- Żadnych zależności od wcześniejszych decyzji
- Kompletny system z gwarancją działania

---

### 💡 WARIANT B: Wykorzystanie Wcześniejszego Kodu (Opcjonalny)

**Założenie:** W poprzedniej fazie projektu był już implementowany system zbierania danych do faktury. Jeśli zdecydują się Państwo zmergować ten kod PRZED rozpoczęciem prac nad PDF, możemy zaoszczędzić czas i koszty.

**Co JUŻ JEST zrobione (jeśli merge):**
- Formularz "Potrzebuję faktury" w kreatorze rezerwacji
- Pola: NIP, nazwa firmy, adres
- Walidacja NIP (polski format z checksum)
- Zapisywanie danych klienta w bazie
- 36 testów zapewniających jakość

**Co TRZEBA dodać:**
- Panel "Dane firmy" dla ParaDocks
- Generowanie PDF z fakturą
- Automatyczna numeracja
- Email z PDF
- Rozszerzenie testów

**Czas:** 30 godzin roboczych (10 dni)

**Cennik:**

| Opcja | Stawka | Koszt Netto | Koszt Brutto (VAT 23%) |
|-------|--------|-------------|------------------------|
| **Z Rabatem** | 85 PLN/h | **2,550 PLN** | **3,137 PLN** ⭐ |
| **Standard** | 100 PLN/h | **3,000 PLN** | **3,690 PLN** |

**Oszczędność:** 1,500-2,000 PLN vs Wariant A

**Dlaczego tańsze?**
- Wykorzystujemy 15-20 godzin gotowego kodu
- Mniejsze ryzyko błędów (kod już przetestowany)
- Szybsza realizacja

---

## 🤔 Który wariant wybrać?

### Wybierz WARIANT A jeśli:
- ✅ Nie chcą Państwo mergować wcześniejszego kodu
- ✅ Wolą Państwo mieć wszystko zrobione "na świeżo"
- ✅ Nie zależy Państwu na czasie (12-14 dni vs 10 dni)

### Wybierz WARIANT B jeśli:
- ✅ Zgadzają się Państwo na merge wcześniejszego kodu do systemu
- ✅ Chcą Państwo zaoszczędzić 1,500-2,000 PLN
- ✅ Zależy Państwu na szybszej realizacji

**WAŻNE:** Nie musicie decydować o merge teraz! Możecie to zrobić później, przed rozpoczęciem prac. Jeśli zdecydują się Państwo na Wariant A, zawsze możemy przejść na Wariant B później (ale nie odwrotnie).

---

## Jak długo to potrwa?

**Wariant A:** 12-14 dni roboczych (średnio 4 godziny dziennie)
**Wariant B:** 10 dni roboczych (średnio 3 godziny dziennie)

**Harmonogram (Wariant A - od zera):**
- **Tydzień 1 (dni 1-7):** Zbieranie danych + walidacja NIP + panel firmy
- **Tydzień 2 (dni 8-14):** Generowanie PDF + email + testy + deployment

**Harmonogram (Wariant B - z reuse):**
- **Tydzień 1 (dni 1-5):** Panel firmy + generowanie PDF
- **Tydzień 2 (dni 6-10):** Email + testy + deployment

**Checkpointy (oba warianty):**
- **Co 2-3 dni:** Pokażę postęp prac
- **Przed deploymentem:** Kompletny system do przetestowania
- **Finalny:** Deployment na produkcję

---

## Co NIE jest wliczone w cenę?

**Dodatkowe koszty (jeśli będą potrzebne w przyszłości):**
- Zmiana wyglądu szablonu faktury (po zaakceptowaniu standardowego) - 50 PLN/h
- Faktury korygujące - osobna wycena (~8 godzin)
- Integracja z systemami księgowymi - osobna wycena

**Wliczone w cenę (oba warianty):**
- Standardowy profesjonalny szablon faktury VAT
- Wszystkie wymagane elementy prawne (zgodność z Art. 106e VAT)
- 30 dni gwarancji (bezpłatne poprawki błędów)
- 90 dni wsparcia technicznego (odpowiedź w 48h)

---

## Co muszą Państwo przygotować?

Żeby zacząć implementację, będę potrzebował następujących informacji:

**Dane firmy (do wyświetlenia na fakturze):**
- Pełna nazwa firmy
- NIP
- REGON
- Adres (ulica, numer, kod pocztowy, miasto)
- Numer konta bankowego (do wpłat)
- Logo firmy (plik PNG lub JPG, najlepiej na przezroczystym tle)

Nie muszą Państwo wysyłać tego od razu - wystarczy do **Dnia 1** implementacji. Mogę też dodać placeholder na początku, a Państwo uzupełnią dane później w panelu admina.

---

## Forma płatności

### Opcja A: Całość z góry (POLECAM)

Całkowita kwota przed rozpoczęciem pracy

**Bonus:** Priorytetowe wsparcie przez 30 dni po wdrożeniu (odpowiedź w 24h zamiast 48h)

### Opcja B: Etapami (50% + 50%)

- **Płatność 1:** 50% przed rozpoczęciem (po akceptacji oferty)
- **Płatność 2:** 50% po finalnym checkpoincie (gdy system będzie gotowy do testowania)

**Metody płatności:** Przelew tradycyjny lub BLIK

---

## Gwarancja i wsparcie

**30 dni gwarancji:**
Bezpłatne poprawki błędów (jeśli coś nie działa zgodnie z opisem)

**90 dni wsparcia:**
Pomoc techniczna email/chat (odpowiedź w 48h)
- Pytania dotyczące obsługi systemu
- Pomoc w konfiguracji
- Porady techniczne

**Dokumentacja:**
- Instrukcja obsługi dla adminów (krok po kroku)
- FAQ (najczęściej zadawane pytania)
- Instrukcja instalacji (dla przyszłych developerów)

---

## Odpowiedzi na pytania

### Q: Jak działa automatyczna numeracja?

System sam nadaje numery w formacie FV/2025/12/0001, FV/2025/12/0002, etc. Co miesiąc numeracja zaczyna się od nowa (styczeń: 0001, luty: 0001). Nie musicie o to dbać - system zadba o sekwencyjność.

### Q: Co jeśli wygenerujemy fakturę z błędem?

Faktury VAT prawnie nie mogą być edytowane. Jeśli wystąpi błąd, możecie usunąć fakturę i wygenerować nową. Luka w numeracji jest prawnie OK (np. FV/2025/12/0001, FV/2025/12/0003 - brak 0002 nie jest problemem).

W przyszłości można dodać funkcję "Faktura korygująca", ale to osobna wycena (nie jest w tej ofercie).

### Q: Czy mogę zobaczyć przykład faktury przed rozpoczęciem?

Oczywiście! W trakcie implementacji pokażę Państwu wygenerowaną fakturę z Państwa danymi (lub placeholder, jeśli dane nie będą jeszcze dostępne). Będziecie mogli zaakceptować wygląd lub zgłosić uwagi.

### Q: Czy klienci będą mogli edytować fakturę?

Nie - faktury są niezmienne (wymóg prawny). Klienci mogą tylko pobrać PDF. Jeśli klient zgłosi błąd w danych, musicie wygenerować nową fakturę.

---

## Co dalej?

Jeśli oferta Państwa zainteresowała, proszę o odpowiedź z następującymi informacjami:

**1. Który wariant?**
- [ ] **Wariant A: Od zera** (4,500-5,000 PLN / 12-14 dni) - bezpieczny, pewny rezultat
- [ ] **Wariant B: Z wcześniejszym kodem** (2,550-3,000 PLN / 10 dni) - oszczędność 1,500 PLN

**2. Opcja cenowa (w ramach wybranego wariantu)?**
- [ ] Z rabatem (85 PLN/h) - tylko Wariant B
- [ ] Standard (100 PLN/h)
- [ ] Premium (120 PLN/h)

**3. Forma płatności?**
- [ ] Całość z góry (bonus: priorytet 30 dni)
- [ ] Etapami (50% + 50%)

**4. Kiedy chcielibyście zacząć?**
- [ ] ASAP (po płatności)
- [ ] Konkretna data: _______

**5. Czy dane firmy są gotowe?**
- [ ] TAK - wyślę do Dnia 1
- [ ] NIE - dostarczę później (mogę dodać placeholder)

---

## Dlaczego warto?

**Oszczędność czasu:**
Obecnie wystawienie faktury zajmuje ~25 minut. Po implementacji: **30 sekund**.

**Eliminacja błędów:**
Nie trzeba przepisywać danych ręcznie, nie trzeba liczyć VAT kalkulatorem. System robi to automatycznie.

**Profesjonalizm:**
Spójny wygląd faktur, logo firmy, zgodność z przepisami. To buduje zaufanie klientów.

**Wygoda dla klientów:**
Klienci pobierają faktury sami z profilu - nie musicie wysyłać emailem, nie musicie pamiętać.

---

Jeśli mają Państwo pytania, chętnie odpowiem. Czekam na odpowiedź!

Pozdrawiam,
Senior Laravel Developer

---

**P.S.**
Oferta ważna do **31 stycznia 2025**. Cena może ulec zmianie po tej dacie (wzrost kosztów utrzymania).

**P.P.S.**
Jeśli decydują się Państwo na **Wariant B** (z wcześniejszym kodem) przed **31 grudnia 2024**, dodam bonus: **priorytetowe wsparcie przez 60 dni** (zamiast 30) za darmo. To mój prezent świąteczny :)
