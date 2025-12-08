<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed car detailing services (production lookup data).
     *
     * This seeder is idempotent - can be run multiple times safely.
     * Uses 'name' field as unique key for updateOrCreate.
     */
    public function run(): void
    {
        $services = [
            [
                'name' => 'Mycie podstawowe',
                'slug' => 'mycie-podstawowe',
                'description' => 'Podstawowe mycie zewnętrzne i wewnętrzne samochodu',
                'excerpt' => 'Szybkie i skuteczne mycie zewnętrzne oraz podstawowe wnętrza',
                'body' => '<p>Mycie podstawowe to idealne rozwiązanie dla właścicieli aut, którzy cenią sobie czystość i estetykę swojego pojazdu. Usługa obejmuje dokładne mycie nadwozia, czyszczenie felg oraz podstawowe odkurzanie wnętrza. Nasi specjaliści zadbają o to, aby Twoje auto wyglądało świeżo i czystości.</p>',
                'content' => null,
                'meta_title' => 'Mycie podstawowe - Paradocks',
                'meta_description' => 'Szybkie i skuteczne mycie zewnętrzne oraz podstawowe wnętrza. Cena: 150 PLN. Czas: 60 min.',
                'featured_image' => null,
                'published_at' => now(),
                'duration_minutes' => 60,
                'price' => 150.00,
                'price_from' => null,
                'area_served' => 'Poznań',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Mycie premium',
                'slug' => 'mycie-premium',
                'description' => 'Dokładne mycie zewnętrzne i wewnętrzne z odkurzaniem i czyszczeniem tapicerki',
                'excerpt' => 'Kompleksowe mycie ze szczególną dbałością o detale i wykończenie',
                'body' => '<p>Mycie premium to zaawansowana usługa dla wymagających klientów. Oprócz standardowego mycia nadwozia, oferujemy dokładne czyszczenie wszystkich elementów zewnętrznych oraz kompleksowe sprzątanie wnętrza. Czyszczenie kokpitu, tapicerki i wykładzin podłogowych sprawia, że Twoje auto wygląda jak nowe.</p>',
                'content' => null,
                'meta_title' => 'Mycie premium - Paradocks',
                'meta_description' => 'Kompleksowe mycie ze szczególną dbałością o detale i wykończenie. Cena: 250 PLN. Czas: 2 godziny.',
                'featured_image' => null,
                'published_at' => now(),
                'duration_minutes' => 120,
                'price' => 250.00,
                'price_from' => null,
                'area_served' => 'Poznań',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Korekta lakieru',
                'slug' => 'korekta-lakieru',
                'description' => 'Profesjonalna korekta lakieru - usuwanie zarysowań i hologramów',
                'excerpt' => 'Usuwanie zarysowań, matowienia i przywracanie głębi koloru',
                'body' => '<p>Korekta lakieru to zaawansowana usługa polegająca na usunięciu mikro zarysowań, hologramów i matowienia powłoki lakierniczej. Dzięki zastosowaniu profesjonalnych maszyn polerskich oraz najwyższej jakości past, przywracamy lakierowi głębię koloru i lustrzany połysk. Proces wykonywany jest etapami z kontrolą grubości lakieru.</p>',
                'content' => null,
                'meta_title' => 'Korekta lakieru - Paradocks',
                'meta_description' => 'Usuwanie zarysowań, matowienia i przywracanie głębi koloru. Cena: 800 PLN. Czas: 4 godziny.',
                'featured_image' => null,
                'published_at' => now(),
                'duration_minutes' => 240,
                'price' => 800.00,
                'price_from' => 800.00,
                'area_served' => 'Poznań',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Powłoka ceramiczna',
                'slug' => 'powloka-ceramiczna',
                'description' => 'Aplikacja ceramicznej powłoki ochronnej na lakier',
                'excerpt' => 'Długotrwała ochrona lakieru z efektem hydrofobowym',
                'body' => '<p>Powłoka ceramiczna to najlepsza forma ochrony lakieru dostępna na rynku. Tworzy trwałą warstwę ochronną, która zabezpiecza przed zanieczyszczeniami, promieniowaniem UV, oraz wpływem chemikaliów drogowych. Dodatkowo nadaje głęboki połysk i efekt hydrofobowy (wodoodporność). Trwałość powłoki to nawet 3-5 lat przy odpowiedniej pielęgnacji.</p>',
                'content' => null,
                'meta_title' => 'Powłoka ceramiczna - Paradocks',
                'meta_description' => 'Długotrwała ochrona lakieru z efektem hydrofobowym. Cena: od 1200 PLN. Czas: 3 godziny.',
                'featured_image' => null,
                'published_at' => now(),
                'duration_minutes' => 180,
                'price' => 1200.00,
                'price_from' => 1200.00,
                'area_served' => 'Poznań',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Wosk na gorąco',
                'slug' => 'wosk-na-goraco',
                'description' => 'Aplikacja wosku na gorąco dla ochrony i połysku lakieru',
                'excerpt' => 'Naturalna ochrona i niesamowity połysk',
                'body' => '<p>Wosk na gorąco to sprawdzona metoda zabezpieczenia lakieru przed czynnikami zewnętrznymi. Aplikowany w podwyższonej temperaturze, wosk głębiej penetruje strukturę lakieru, tworząc warstwę ochronną. Efekt to piękny, głęboki połysk oraz ochrona trwająca do kilku miesięcy. Polecane dla klientów ceniących tradycyjne metody detailingu.</p>',
                'content' => null,
                'meta_title' => 'Wosk na gorąco - Paradocks',
                'meta_description' => 'Naturalna ochrona i niesamowity połysk. Cena: 200 PLN. Czas: 90 minut.',
                'featured_image' => null,
                'published_at' => now(),
                'duration_minutes' => 90,
                'price' => 200.00,
                'price_from' => null,
                'area_served' => 'Poznań',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Pranie tapicerki',
                'slug' => 'pranie-tapicerki',
                'description' => 'Głębokie czyszczenie i pranie tapicerki materiałowej lub skórzanej',
                'excerpt' => 'Usuwanie zabrudzeń, plam i nieprzyjemnych zapachów',
                'body' => '<p>Pranie tapicerki to kompleksowa usługa czyszczenia wnętrza samochodu. Wykorzystujemy profesjonalne ekstraktory parowe oraz specjalistyczne środki czyszczące do usuwania nawet najtrudniejszych zabrudzeń. Proces obejmuje pranie foteli, podsufitki, wykładzin i plastików. Po usłudze wnętrze jest odświeżone, a wszystkie nieprzyjemne zapachy znikają.</p>',
                'content' => null,
                'meta_title' => 'Pranie tapicerki - Paradocks',
                'meta_description' => 'Usuwanie zabrudzeń, plam i nieprzyjemnych zapachów. Cena: 350 PLN. Czas: 2 godziny.',
                'featured_image' => null,
                'published_at' => now(),
                'duration_minutes' => 120,
                'price' => 350.00,
                'price_from' => null,
                'area_served' => 'Poznań',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Czyszczenie silnika',
                'slug' => 'czyszczenie-silnika',
                'description' => 'Profesjonalne czyszczenie komory silnika',
                'excerpt' => 'Czysty silnik to gwarancja estetyki i łatwiejszej diagnostyki',
                'body' => '<p>Czyszczenie komory silnika to usługa zwiększająca estetykę pojazdu oraz ułatwiająca ewentualne naprawy i diagnostykę. Usuwamy tłuszcz, brud i resztki olejów, używając bezpiecznych środków czyszczących. Proces kończy się zabezpieczeniem elementów gumowych i plastikowych, co przedłuża ich żywotność. Polecane szczególnie przed przeglądem technicznym lub sprzedażą auta.</p>',
                'content' => null,
                'meta_title' => 'Czyszczenie silnika - Paradocks',
                'meta_description' => 'Czysty silnik to gwarancja estetyki i łatwiejszej diagnostyki. Cena: 150 PLN. Czas: 60 minut.',
                'featured_image' => null,
                'published_at' => now(),
                'duration_minutes' => 60,
                'price' => 150.00,
                'price_from' => null,
                'area_served' => 'Poznań',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Detailing kompletny',
                'slug' => 'detailing-kompletny',
                'description' => 'Kompleksowy pakiet detailingu - mycie, korekta, powłoka ceramiczna, pranie wnętrza',
                'excerpt' => 'Najwyższa jakość - kompleksowa renowacja auta od A do Z',
                'body' => '<p>Detailing kompletny to flagowa usługa dla najbardziej wymagających klientów. Pakiet obejmuje pełną korektrę lakieru, aplikację powłoki ceramicznej, kompleksowe pranie wnętrza oraz czyszczenie wszystkich elementów zewnętrznych i wewnętrznych. Auto wychodzi ze studia w stanie lepszym niż salonowe. To inwestycja w długotrwałą ochronę i estetykę pojazdu.</p>',
                'content' => null,
                'meta_title' => 'Detailing kompletny - Paradocks',
                'meta_description' => 'Najwyższa jakość - kompleksowa renowacja auta od A do Z. Cena: od 2500 PLN. Czas: 8 godzin.',
                'featured_image' => null,
                'published_at' => now(),
                'duration_minutes' => 480,
                'price' => 2500.00,
                'price_from' => 2500.00,
                'area_served' => 'Poznań',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($services as $serviceData) {
            Service::updateOrCreate(
                ['name' => $serviceData['name']],
                $serviceData
            );
        }

        $this->command->info('Services seeded successfully!');
    }
}
