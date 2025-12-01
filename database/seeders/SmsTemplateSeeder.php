<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SmsTemplate;
use Illuminate\Database\Seeder;

/**
 * SMS Template Seeder
 *
 * Seeds transactional SMS templates (production lookup data).
 *
 * Creates 14 SMS templates covering 7 event types in 2 languages (PL, EN):
 * 1. appointment-created - Booking created confirmation
 * 2. appointment-confirmed - Admin confirmation
 * 3. appointment-rescheduled - Date/time change notification
 * 4. appointment-cancelled - Cancellation notification
 * 5. appointment-reminder-24h - 24-hour reminder
 * 6. appointment-reminder-2h - 2-hour reminder
 * 7. appointment-followup - Post-service follow-up
 *
 * Each template is 160 characters or less (single SMS).
 * Includes variable placeholders: {{customer_name}}, {{date}}, {{time}}, etc.
 *
 * This seeder is idempotent - can be run multiple times safely.
 */
class SmsTemplateSeeder extends Seeder
{
    /**
     * Seed SMS templates for all transactional messages.
     */
    public function run(): void
    {
        $templates = [
            // 1. Appointment Created - Booking Confirmation
            [
                'key' => 'appointment-created',
                'language' => 'pl',
                'message_body' => 'Witaj {{customer_name}}! Rezerwacja na {{service_name}} dnia {{appointment_date}} o {{appointment_time}} utworzona. {{app_name}}',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],
            [
                'key' => 'appointment-created',
                'language' => 'en',
                'message_body' => 'Hi {{customer_name}}! Your {{service_name}} booking on {{appointment_date}} at {{appointment_time}} is confirmed. {{app_name}}',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],

            // 2. Appointment Confirmed - Admin Confirmation
            [
                'key' => 'appointment-confirmed',
                'language' => 'pl',
                'message_body' => 'Witaj {{customer_name}}! Twoja wizyta ({{service_name}}) {{appointment_date}} o {{appointment_time}} potwierdzona. Do zobaczenia! {{app_name}}',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],
            [
                'key' => 'appointment-confirmed',
                'language' => 'en',
                'message_body' => 'Hi {{customer_name}}! Your appointment ({{service_name}}) on {{appointment_date}} at {{appointment_time}} is confirmed. See you! {{app_name}}',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],

            // 3. Appointment Rescheduled - Date/Time Change
            [
                'key' => 'appointment-rescheduled',
                'language' => 'pl',
                'message_body' => 'Witaj {{customer_name}}! Twoja wizyta ({{service_name}}) przeniesiona na {{appointment_date}} o {{appointment_time}}. {{app_name}}',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],
            [
                'key' => 'appointment-rescheduled',
                'language' => 'en',
                'message_body' => 'Hi {{customer_name}}! Your appointment ({{service_name}}) has been rescheduled to {{appointment_date}} at {{appointment_time}}. {{app_name}}',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],

            // 4. Appointment Cancelled - Cancellation Notice
            [
                'key' => 'appointment-cancelled',
                'language' => 'pl',
                'message_body' => 'Witaj {{customer_name}}! Twoja wizyta ({{service_name}}) {{appointment_date}} o {{appointment_time}} anulowana. Kontakt: {{contact_phone}}. {{app_name}}',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'contact_phone', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],
            [
                'key' => 'appointment-cancelled',
                'language' => 'en',
                'message_body' => 'Hi {{customer_name}}! Your appointment ({{service_name}}) on {{appointment_date}} at {{appointment_time}} has been cancelled. Contact: {{contact_phone}}. {{app_name}}',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'contact_phone', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],

            // 5. Appointment Reminder 24h - Day Before Reminder
            [
                'key' => 'appointment-reminder-24h',
                'language' => 'pl',
                'message_body' => 'Przypomnienie! Jutro masz wizyte: {{service_name}}, {{appointment_date}} o {{appointment_time}}. Lokalizacja: {{location_address}}. {{app_name}}',
                'variables' => ['service_name', 'appointment_date', 'appointment_time', 'location_address', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],
            [
                'key' => 'appointment-reminder-24h',
                'language' => 'en',
                'message_body' => 'Reminder! Your appointment tomorrow: {{service_name}}, {{appointment_date}} at {{appointment_time}}. Location: {{location_address}}. {{app_name}}',
                'variables' => ['service_name', 'appointment_date', 'appointment_time', 'location_address', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],

            // 6. Appointment Reminder 2h - Same Day Reminder
            [
                'key' => 'appointment-reminder-2h',
                'language' => 'pl',
                'message_body' => 'Przypomnienie! Za 2h wizyta: {{service_name}} o {{appointment_time}}. Lokalizacja: {{location_address}}. Do zobaczenia! {{app_name}}',
                'variables' => ['service_name', 'appointment_time', 'location_address', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],
            [
                'key' => 'appointment-reminder-2h',
                'language' => 'en',
                'message_body' => 'Reminder! In 2 hours: {{service_name}} at {{appointment_time}}. Location: {{location_address}}. See you soon! {{app_name}}',
                'variables' => ['service_name', 'appointment_time', 'location_address', 'app_name'],
                'max_length' => 160,
                'active' => true,
            ],

            // 7. Appointment Follow-up - Post-Service Feedback Request
            [
                'key' => 'appointment-followup',
                'language' => 'pl',
                'message_body' => 'Witaj {{customer_name}}! Dziekujemy za skorzystanie z {{service_name}}. Bylibyśmy wdzieczni za opinie. {{app_name}} {{contact_phone}}',
                'variables' => ['customer_name', 'service_name', 'app_name', 'contact_phone'],
                'max_length' => 160,
                'active' => true,
            ],
            [
                'key' => 'appointment-followup',
                'language' => 'en',
                'message_body' => 'Hi {{customer_name}}! Thank you for using {{service_name}}. We would appreciate your feedback. {{app_name}} {{contact_phone}}',
                'variables' => ['customer_name', 'service_name', 'app_name', 'contact_phone'],
                'max_length' => 160,
                'active' => true,
            ],
        ];

        // Seed templates using updateOrCreate for idempotency
        foreach ($templates as $template) {
            SmsTemplate::updateOrCreate(
                [
                    'key' => $template['key'],
                    'language' => $template['language'],
                ],
                $template
            );
        }

        $this->command->info('✓ SMS templates seeded successfully');
        $this->command->info('✓ Created 14 templates (7 types × 2 languages: PL, EN)');
        $this->command->line('');
        $this->command->table(
            ['Template Key', 'Languages', 'Variables'],
            [
                ['appointment-created', 'PL, EN', 'customer_name, service_name, appointment_date, appointment_time, app_name'],
                ['appointment-confirmed', 'PL, EN', 'customer_name, service_name, appointment_date, appointment_time, app_name'],
                ['appointment-rescheduled', 'PL, EN', 'customer_name, service_name, appointment_date, appointment_time, app_name'],
                ['appointment-cancelled', 'PL, EN', 'customer_name, service_name, appointment_date, appointment_time, contact_phone, app_name'],
                ['appointment-reminder-24h', 'PL, EN', 'service_name, appointment_date, appointment_time, location_address, app_name'],
                ['appointment-reminder-2h', 'PL, EN', 'service_name, appointment_time, location_address, app_name'],
                ['appointment-followup', 'PL, EN', 'customer_name, service_name, app_name, contact_phone'],
            ]
        );
    }
}
