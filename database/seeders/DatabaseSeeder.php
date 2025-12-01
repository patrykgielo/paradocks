<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with production lookup data.
     *
     * This seeder orchestrates all production-safe seeders that populate
     * essential lookup tables required for application functionality.
     *
     * IMPORTANT: This seeder does NOT create users!
     * For first admin user, use: php artisan make:filament-user
     *
     * What gets seeded (v0.3.0):
     * - Application settings (booking, map, contact, marketing)
     * - Roles and permissions (super-admin, admin, staff, customer)
     * - Vehicle types (5 categories for booking wizard)
     * - Services (8 car detailing services)
     * - Email templates (28 templates: 14 types × 2 languages)
     * - SMS templates (14 templates: 7 types × 2 languages)
     *
     * All seeders are idempotent - safe to run multiple times.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            RolePermissionSeeder::class,
            VehicleTypeSeeder::class,
            ServiceSeeder::class,
            EmailTemplateSeeder::class,
            SmsTemplateSeeder::class,
        ]);

        // NOTE: Test users are created by individual tests via factories
        // For manual testing, use: php artisan make:filament-user
        // Or create via Tinker: User::factory()->create([...])
    }
}
