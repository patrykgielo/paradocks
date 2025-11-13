<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Setting Seeder
 *
 * Seeds default application settings for all groups:
 * - booking: Business hours, slot intervals, cancellation policies
 * - map: Google Maps configuration
 * - contact: Business contact information
 * - marketing: Homepage content and messaging
 * - email: SMTP configuration and notification settings
 * - sms: SMSAPI configuration and notification settings
 */
class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedBookingSettings();
        $this->seedMapSettings();
        $this->seedContactSettings();
        $this->seedMarketingSettings();
        $this->seedEmailSettings();
        $this->seedSmsSettings();
    }

    /**
     * Seed booking configuration settings.
     */
    private function seedBookingSettings(): void
    {
        $settings = [
            'business_hours_start' => ['09:00'],
            'business_hours_end' => ['18:00'],
            'slot_interval_minutes' => [30],
            'advance_booking_hours' => [24],
            'cancellation_hours' => [24],
            'max_service_duration_minutes' => [480],
        ];

        $this->seedGroup('booking', $settings);
    }

    /**
     * Seed Google Maps configuration settings.
     */
    private function seedMapSettings(): void
    {
        $settings = [
            'default_latitude' => [52.2297],
            'default_longitude' => [21.0122],
            'default_zoom' => [13],
            'country_code' => ['pl'],
            'map_id' => [null],
            'debug_panel_enabled' => [false],
        ];

        $this->seedGroup('map', $settings);
    }

    /**
     * Seed business contact information settings.
     */
    private function seedContactSettings(): void
    {
        $settings = [
            'email' => ['contact@example.com'],
            'phone' => ['+48123456789'],
            'address_line' => ['ul. Marszałkowska 1'],
            'city' => ['Warszawa'],
            'postal_code' => ['00-001'],
        ];

        $this->seedGroup('contact', $settings);
    }

    /**
     * Seed marketing content settings.
     */
    private function seedMarketingSettings(): void
    {
        $settings = [
            'hero_title' => ['Profesjonalne Pranie Tapicerki Samochodowej'],
            'hero_subtitle' => ['Przywróć swojemu samochodowi pierwotny wygląd'],
            'services_heading' => ['Nasze Usługi'],
            'services_subheading' => ['Kompleksowa oferta detailingu'],
            'features_heading' => ['Dlaczego My?'],
            'features_subheading' => ['Gwarantujemy najwyższą jakość'],
            'features' => [
                [
                    ['title' => 'Profesjonalny Sprzęt', 'description' => 'Używamy najnowocześniejszego sprzętu do prania tapicerki'],
                    ['title' => 'Doświadczony Zespół', 'description' => 'Nasz zespół ma wieloletnie doświadczenie'],
                    ['title' => 'Gwarancja Jakości', 'description' => 'Gwarantujemy 100% satysfakcji'],
                ]
            ],
            'cta_heading' => ['Umów się już dziś'],
            'cta_subheading' => ['Skontaktuj się z nami i poznaj naszą ofertę'],
            'important_info_heading' => ['Ważne Informacje'],
            'important_info_points' => [
                [
                    'Rezerwacja wymaga wpłaty zaliczki',
                    'Możliwość anulacji do 24h przed wizytą',
                    'Usługi realizowane na terenie klienta',
                ]
            ],
        ];

        $this->seedGroup('marketing', $settings);
    }

    /**
     * Seed email system configuration settings.
     */
    private function seedEmailSettings(): void
    {
        $settings = [
            'smtp_host' => ['smtp.gmail.com'],
            'smtp_port' => [587],
            'smtp_encryption' => ['tls'],
            'smtp_username' => [null],
            'smtp_password' => [null],
            'from_name' => ['Paradocks'],
            'from_address' => ['noreply@paradocks.local'],
            'retry_attempts' => [3],
            'backoff_seconds' => [60],
            'reminder_24h_enabled' => [true],
            'reminder_2h_enabled' => [true],
            'followup_enabled' => [true],
            'admin_digest_enabled' => [true],
        ];

        $this->seedGroup('email', $settings);
    }

    /**
     * Seed SMS system configuration settings.
     */
    private function seedSmsSettings(): void
    {
        $settings = [
            'enabled' => [true],
            'api_token' => [null],
            'service' => ['pl'],
            'sender_name' => ['Paradocks'],
            'test_mode' => [false],
            'send_booking_confirmation' => [true],
            'send_admin_confirmation' => [true],
            'send_reminder_24h' => [true],
            'send_reminder_2h' => [true],
            'send_follow_up' => [true],
        ];

        $this->seedGroup('sms', $settings);
    }

    /**
     * Helper method to seed a group of settings.
     *
     * @param string $group Group name
     * @param array<string, array> $settings Key-value pairs (values already wrapped in arrays)
     */
    private function seedGroup(string $group, array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['group' => $group, 'key' => $key],
                ['value' => $value]
            );
        }

        $this->command->info("✓ Seeded {$group} settings");
    }
}
