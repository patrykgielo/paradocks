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
            RolePermissionSeeder::class,
            VehicleTypeSeeder::class,
            EmailTemplateSeeder::class,
            SmsTemplateSeeder::class,
        ]);

        // NOTE: Test users are created by individual tests via factories
        // For manual testing, use: php artisan make:filament-user
        // Or create via Tinker: User::factory()->create([...])
    }
}
