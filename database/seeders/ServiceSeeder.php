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
                'description' => 'Podstawowe mycie zewnętrzne i wewnętrzne samochodu',
                'duration_minutes' => 60,
                'price' => 150.00,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Mycie premium',
                'description' => 'Dokładne mycie zewnętrzne i wewnętrzne z odkurzaniem i czyszczeniem tapicerki',
                'duration_minutes' => 120,
                'price' => 250.00,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Korekta lakieru',
                'description' => 'Profesjonalna korekta lakieru - usuwanie zarysowań i hologramów',
                'duration_minutes' => 240,
                'price' => 800.00,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Powłoka ceramiczna',
                'description' => 'Aplikacja ceramicznej powłoki ochronnej na lakier',
                'duration_minutes' => 180,
                'price' => 1200.00,
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Wosk na gorąco',
                'description' => 'Aplikacja wosku na gorąco dla ochrony i połysku lakieru',
                'duration_minutes' => 90,
                'price' => 200.00,
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Pranie tapicerki',
                'description' => 'Głębokie czyszczenie i pranie tapicerki materiałowej lub skórzanej',
                'duration_minutes' => 120,
                'price' => 350.00,
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Czyszczenie silnika',
                'description' => 'Profesjonalne czyszczenie komory silnika',
                'duration_minutes' => 60,
                'price' => 150.00,
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Detailing kompletny',
                'description' => 'Kompleksowy pakiet detailingu - mycie, korekta, powłoka ceramiczna, pranie wnętrza',
                'duration_minutes' => 480,
                'price' => 2500.00,
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
