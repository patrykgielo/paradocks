<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
        ]);

        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone_e164' => '+48501234567',
            'street_name' => 'Testowa',
            'street_number' => '1',
            'city' => 'Warszawa',
            'postal_code' => '00-000',
        ]);
    }
}
