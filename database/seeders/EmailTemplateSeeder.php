<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

/**
 * Email Template Seeder
 *
 * Seeds 18 email templates (9 types × 2 languages: PL, EN)
 */
class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            // 1. User Registered - Welcome Email
            [
                'key' => 'user-registered',
                'language' => 'pl',
                'subject' => 'Witamy w {{app_name}}!',
                'html_body' => '<h1>Witaj {{user_name}}!</h1><p>Dziękujemy za rejestrację w {{app_name}}.</p><p>Twój adres email: <strong>{{user_email}}</strong></p><p>Cieszymy się, że do nas dołączyłeś!</p><p>Pozdrawiamy,<br>Zespół {{app_name}}</p>',
                'text_body' => 'Witaj {{user_name}}! Dziękujemy za rejestrację w {{app_name}}. Twój adres email: {{user_email}}. Cieszymy się, że do nas dołączyłeś! Pozdrawiamy, Zespół {{app_name}}',
                'blade_path' => 'emails.user-registered-pl',
                'variables' => ['user_name', 'app_name', 'user_email'],
                'active' => true,
            ],
            [
                'key' => 'user-registered',
                'language' => 'en',
                'subject' => 'Welcome to {{app_name}}!',
                'html_body' => '<h1>Hello {{user_name}}!</h1><p>Thank you for registering with {{app_name}}.</p><p>Your email address: <strong>{{user_email}}</strong></p><p>We are excited to have you on board!</p><p>Best regards,<br>The {{app_name}} Team</p>',
                'text_body' => 'Hello {{user_name}}! Thank you for registering with {{app_name}}. Your email address: {{user_email}}. We are excited to have you on board! Best regards, The {{app_name}} Team',
                'blade_path' => 'emails.user-registered-en',
                'variables' => ['user_name', 'app_name', 'user_email'],
                'active' => true,
            ],

            // 2. Password Reset
            [
                'key' => 'password-reset',
                'language' => 'pl',
                'subject' => 'Resetowanie hasła - {{app_name}}',
                'html_body' => '<h1>Witaj {{user_name}},</h1><p>Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta.</p><p><a href="{{reset_url}}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Zresetuj hasło</a></p><p>Jeśli nie prosiłeś o zmianę hasła, zignoruj tę wiadomość.</p><p>Link jest ważny przez {{expires_in}} minut.</p><p>Pozdrawiamy,<br>Zespół {{app_name}}</p>',
                'text_body' => 'Witaj {{user_name}}, Otrzymaliśmy prośbę o zresetowanie hasła do Twojego konta. Kliknij link: {{reset_url}}. Jeśli nie prosiłeś o zmianę hasła, zignoruj tę wiadomość. Link jest ważny przez {{expires_in}} minut. Pozdrawiamy, Zespół {{app_name}}',
                'blade_path' => 'emails.password-reset-pl',
                'variables' => ['user_name', 'app_name', 'reset_url', 'expires_in'],
                'active' => true,
            ],
            [
                'key' => 'password-reset',
                'language' => 'en',
                'subject' => 'Password Reset - {{app_name}}',
                'html_body' => '<h1>Hello {{user_name}},</h1><p>We received a request to reset your password.</p><p><a href="{{reset_url}}" style="background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Reset Password</a></p><p>If you did not request a password reset, please ignore this email.</p><p>This link is valid for {{expires_in}} minutes.</p><p>Best regards,<br>The {{app_name}} Team</p>',
                'text_body' => 'Hello {{user_name}}, We received a request to reset your password. Click here: {{reset_url}}. If you did not request a password reset, please ignore this email. This link is valid for {{expires_in}} minutes. Best regards, The {{app_name}} Team',
                'blade_path' => 'emails.password-reset-en',
                'variables' => ['user_name', 'app_name', 'reset_url', 'expires_in'],
                'active' => true,
            ],

            // 3. Appointment Created - Confirmation
            [
                'key' => 'appointment-created',
                'language' => 'pl',
                'subject' => 'Potwierdzenie rezerwacji - {{service_name}}',
                'html_body' => '<h1>Cześć {{customer_name}}!</h1><p>Twoja rezerwacja na usługę <strong>{{service_name}}</strong> została potwierdzona.</p><p><strong>Data:</strong> {{appointment_date}}<br><strong>Godzina:</strong> {{appointment_time}}<br><strong>Lokalizacja:</strong> {{location_address}}</p><p>Do zobaczenia!</p><p>Pozdrawiamy,<br>Zespół {{app_name}}</p>',
                'text_body' => 'Cześć {{customer_name}}! Twoja rezerwacja na usługę {{service_name}} została potwierdzona. Data: {{appointment_date}}, Godzina: {{appointment_time}}, Lokalizacja: {{location_address}}. Do zobaczenia! Pozdrawiamy, Zespół {{app_name}}',
                'blade_path' => 'emails.appointment-created-pl',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'location_address', 'app_name'],
                'active' => true,
            ],
            [
                'key' => 'appointment-created',
                'language' => 'en',
                'subject' => 'Appointment Confirmation - {{service_name}}',
                'html_body' => '<h1>Hello {{customer_name}}!</h1><p>Your appointment for <strong>{{service_name}}</strong> is confirmed.</p><p><strong>Date:</strong> {{appointment_date}}<br><strong>Time:</strong> {{appointment_time}}<br><strong>Location:</strong> {{location_address}}</p><p>See you soon!</p><p>Best regards,<br>The {{app_name}} Team</p>',
                'text_body' => 'Hello {{customer_name}}! Your appointment for {{service_name}} is confirmed. Date: {{appointment_date}}, Time: {{appointment_time}}, Location: {{location_address}}. See you soon! Best regards, The {{app_name}} Team',
                'blade_path' => 'emails.appointment-created-en',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'location_address', 'app_name'],
                'active' => true,
            ],

            // 4. Appointment Rescheduled
            [
                'key' => 'appointment-rescheduled',
                'language' => 'pl',
                'subject' => 'Zmiana terminu rezerwacji - {{service_name}}',
                'html_body' => '<h1>Cześć {{customer_name}},</h1><p>Informujemy, że termin Twojej rezerwacji uległ zmianie.</p><p><strong>Nowy termin:</strong><br>Data: {{appointment_date}}<br>Godzina: {{appointment_time}}<br>Lokalizacja: {{location_address}}</p><p>Jeśli masz pytania, skontaktuj się z nami.</p><p>Pozdrawiamy,<br>Zespół {{app_name}}</p>',
                'text_body' => 'Cześć {{customer_name}}, Informujemy, że termin Twojej rezerwacji uległ zmianie. Nowy termin: Data: {{appointment_date}}, Godzina: {{appointment_time}}, Lokalizacja: {{location_address}}. Jeśli masz pytania, skontaktuj się z nami. Pozdrawiamy, Zespół {{app_name}}',
                'blade_path' => 'emails.appointment-rescheduled-pl',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'location_address', 'app_name'],
                'active' => true,
            ],
            [
                'key' => 'appointment-rescheduled',
                'language' => 'en',
                'subject' => 'Appointment Rescheduled - {{service_name}}',
                'html_body' => '<h1>Hello {{customer_name}},</h1><p>We would like to inform you that your appointment has been rescheduled.</p><p><strong>New schedule:</strong><br>Date: {{appointment_date}}<br>Time: {{appointment_time}}<br>Location: {{location_address}}</p><p>If you have any questions, please contact us.</p><p>Best regards,<br>The {{app_name}} Team</p>',
                'text_body' => 'Hello {{customer_name}}, We would like to inform you that your appointment has been rescheduled. New schedule: Date: {{appointment_date}}, Time: {{appointment_time}}, Location: {{location_address}}. If you have any questions, please contact us. Best regards, The {{app_name}} Team',
                'blade_path' => 'emails.appointment-rescheduled-en',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'location_address', 'app_name'],
                'active' => true,
            ],

            // 5. Appointment Cancelled
            [
                'key' => 'appointment-cancelled',
                'language' => 'pl',
                'subject' => 'Anulowanie rezerwacji - {{service_name}}',
                'html_body' => '<h1>Cześć {{customer_name}},</h1><p>Twoja rezerwacja na usługę <strong>{{service_name}}</strong> została anulowana.</p><p><strong>Szczegóły anulowanej rezerwacji:</strong><br>Data: {{appointment_date}}<br>Godzina: {{appointment_time}}</p><p>Mamy nadzieję, że skorzystasz z naszych usług w przyszłości.</p><p>Pozdrawiamy,<br>Zespół {{app_name}}</p>',
                'text_body' => 'Cześć {{customer_name}}, Twoja rezerwacja na usługę {{service_name}} została anulowana. Szczegóły: Data: {{appointment_date}}, Godzina: {{appointment_time}}. Mamy nadzieję, że skorzystasz z naszych usług w przyszłości. Pozdrawiamy, Zespół {{app_name}}',
                'blade_path' => 'emails.appointment-cancelled-pl',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'app_name'],
                'active' => true,
            ],
            [
                'key' => 'appointment-cancelled',
                'language' => 'en',
                'subject' => 'Appointment Cancelled - {{service_name}}',
                'html_body' => '<h1>Hello {{customer_name}},</h1><p>Your appointment for <strong>{{service_name}}</strong> has been cancelled.</p><p><strong>Cancelled appointment details:</strong><br>Date: {{appointment_date}}<br>Time: {{appointment_time}}</p><p>We hope to see you again in the future.</p><p>Best regards,<br>The {{app_name}} Team</p>',
                'text_body' => 'Hello {{customer_name}}, Your appointment for {{service_name}} has been cancelled. Details: Date: {{appointment_date}}, Time: {{appointment_time}}. We hope to see you again in the future. Best regards, The {{app_name}} Team',
                'blade_path' => 'emails.appointment-cancelled-en',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'app_name'],
                'active' => true,
            ],

            // 6. Appointment Reminder - 24 Hours
            [
                'key' => 'appointment-reminder-24h',
                'language' => 'pl',
                'subject' => 'Przypomnienie: Jutro Twoja rezerwacja - {{service_name}}',
                'html_body' => '<h1>Cześć {{customer_name}}!</h1><p>Przypominamy, że jutro masz zaplanowaną wizytę.</p><p><strong>Szczegóły:</strong><br>Usługa: {{service_name}}<br>Data: {{appointment_date}}<br>Godzina: {{appointment_time}}<br>Lokalizacja: {{location_address}}</p><p>Do zobaczenia!</p><p>Pozdrawiamy,<br>Zespół {{app_name}}</p>',
                'text_body' => 'Cześć {{customer_name}}! Przypominamy, że jutro masz zaplanowaną wizytę. Szczegóły: Usługa: {{service_name}}, Data: {{appointment_date}}, Godzina: {{appointment_time}}, Lokalizacja: {{location_address}}. Do zobaczenia! Pozdrawiamy, Zespół {{app_name}}',
                'blade_path' => 'emails.appointment-reminder-24h-pl',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'location_address', 'app_name'],
                'active' => true,
            ],
            [
                'key' => 'appointment-reminder-24h',
                'language' => 'en',
                'subject' => 'Reminder: Tomorrow is Your Appointment - {{service_name}}',
                'html_body' => '<h1>Hello {{customer_name}}!</h1><p>This is a reminder that you have an appointment scheduled for tomorrow.</p><p><strong>Details:</strong><br>Service: {{service_name}}<br>Date: {{appointment_date}}<br>Time: {{appointment_time}}<br>Location: {{location_address}}</p><p>See you soon!</p><p>Best regards,<br>The {{app_name}} Team</p>',
                'text_body' => 'Hello {{customer_name}}! This is a reminder that you have an appointment scheduled for tomorrow. Details: Service: {{service_name}}, Date: {{appointment_date}}, Time: {{appointment_time}}, Location: {{location_address}}. See you soon! Best regards, The {{app_name}} Team',
                'blade_path' => 'emails.appointment-reminder-24h-en',
                'variables' => ['customer_name', 'service_name', 'appointment_date', 'appointment_time', 'location_address', 'app_name'],
                'active' => true,
            ],

            // 7. Appointment Reminder - 2 Hours
            [
                'key' => 'appointment-reminder-2h',
                'language' => 'pl',
                'subject' => 'Przypomnienie: Za 2 godziny Twoja rezerwacja - {{service_name}}',
                'html_body' => '<h1>Cześć {{customer_name}}!</h1><p>Przypominamy, że za 2 godziny masz zaplanowaną wizytę.</p><p><strong>Szczegóły:</strong><br>Usługa: {{service_name}}<br>Godzina: {{appointment_time}}<br>Lokalizacja: {{location_address}}</p><p>Do zobaczenia wkrótce!</p><p>Pozdrawiamy,<br>Zespół {{app_name}}</p>',
                'text_body' => 'Cześć {{customer_name}}! Przypominamy, że za 2 godziny masz zaplanowaną wizytę. Szczegóły: Usługa: {{service_name}}, Godzina: {{appointment_time}}, Lokalizacja: {{location_address}}. Do zobaczenia wkrótce! Pozdrawiamy, Zespół {{app_name}}',
                'blade_path' => 'emails.appointment-reminder-2h-pl',
                'variables' => ['customer_name', 'service_name', 'appointment_time', 'location_address', 'app_name'],
                'active' => true,
            ],
            [
                'key' => 'appointment-reminder-2h',
                'language' => 'en',
                'subject' => 'Reminder: Your Appointment in 2 Hours - {{service_name}}',
                'html_body' => '<h1>Hello {{customer_name}}!</h1><p>This is a reminder that you have an appointment in 2 hours.</p><p><strong>Details:</strong><br>Service: {{service_name}}<br>Time: {{appointment_time}}<br>Location: {{location_address}}</p><p>See you very soon!</p><p>Best regards,<br>The {{app_name}} Team</p>',
                'text_body' => 'Hello {{customer_name}}! This is a reminder that you have an appointment in 2 hours. Details: Service: {{service_name}}, Time: {{appointment_time}}, Location: {{location_address}}. See you very soon! Best regards, The {{app_name}} Team',
                'blade_path' => 'emails.appointment-reminder-2h-en',
                'variables' => ['customer_name', 'service_name', 'appointment_time', 'location_address', 'app_name'],
                'active' => true,
            ],

            // 8. Appointment Follow-up - Thank You + Review Request
            [
                'key' => 'appointment-followup',
                'language' => 'pl',
                'subject' => 'Dziękujemy za skorzystanie z naszych usług!',
                'html_body' => '<h1>Cześć {{customer_name}}!</h1><p>Dziękujemy za skorzystanie z usługi <strong>{{service_name}}</strong>.</p><p>Mamy nadzieję, że jesteś zadowolony z naszej pracy. Jeśli masz chwilę, będziemy wdzięczni za zostawienie opinii.</p><p><a href="{{review_url}}" style="background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Zostaw opinię</a></p><p>Do zobaczenia ponownie!</p><p>Pozdrawiamy,<br>Zespół {{app_name}}</p>',
                'text_body' => 'Cześć {{customer_name}}! Dziękujemy za skorzystanie z usługi {{service_name}}. Mamy nadzieję, że jesteś zadowolony z naszej pracy. Jeśli masz chwilę, będziemy wdzięczni za zostawienie opinii: {{review_url}}. Do zobaczenia ponownie! Pozdrawiamy, Zespół {{app_name}}',
                'blade_path' => 'emails.appointment-followup-pl',
                'variables' => ['customer_name', 'service_name', 'review_url', 'app_name'],
                'active' => true,
            ],
            [
                'key' => 'appointment-followup',
                'language' => 'en',
                'subject' => 'Thank You for Choosing Our Services!',
                'html_body' => '<h1>Hello {{customer_name}}!</h1><p>Thank you for using our <strong>{{service_name}}</strong> service.</p><p>We hope you are satisfied with our work. If you have a moment, we would greatly appreciate your feedback.</p><p><a href="{{review_url}}" style="background-color: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">Leave a Review</a></p><p>See you again!</p><p>Best regards,<br>The {{app_name}} Team</p>',
                'text_body' => 'Hello {{customer_name}}! Thank you for using our {{service_name}} service. We hope you are satisfied with our work. If you have a moment, we would greatly appreciate your feedback: {{review_url}}. See you again! Best regards, The {{app_name}} Team',
                'blade_path' => 'emails.appointment-followup-en',
                'variables' => ['customer_name', 'service_name', 'review_url', 'app_name'],
                'active' => true,
            ],

            // 9. Admin Daily Digest - Summary of New Appointments
            [
                'key' => 'admin-daily-digest',
                'language' => 'pl',
                'subject' => 'Dzienny raport rezerwacji - {{date}}',
                'html_body' => '<h1>Raport dzienny</h1><p>Witaj {{admin_name}},</p><p>Oto podsumowanie nowych rezerwacji z dnia <strong>{{date}}</strong>:</p><p><strong>Liczba nowych rezerwacji:</strong> {{appointment_count}}</p><p>{{appointment_list}}</p><p>Zaloguj się do panelu administracyjnego, aby zobaczyć szczegóły.</p><p>Pozdrawiamy,<br>System {{app_name}}</p>',
                'text_body' => 'Raport dzienny. Witaj {{admin_name}}, Oto podsumowanie nowych rezerwacji z dnia {{date}}: Liczba nowych rezerwacji: {{appointment_count}}. {{appointment_list}}. Zaloguj się do panelu administracyjnego, aby zobaczyć szczegóły. Pozdrawiamy, System {{app_name}}',
                'blade_path' => 'emails.admin-daily-digest-pl',
                'variables' => ['admin_name', 'date', 'appointment_count', 'appointment_list', 'app_name'],
                'active' => true,
            ],
            [
                'key' => 'admin-daily-digest',
                'language' => 'en',
                'subject' => 'Daily Appointment Report - {{date}}',
                'html_body' => '<h1>Daily Report</h1><p>Hello {{admin_name}},</p><p>Here is a summary of new appointments for <strong>{{date}}</strong>:</p><p><strong>Number of new appointments:</strong> {{appointment_count}}</p><p>{{appointment_list}}</p><p>Log in to the admin panel to see details.</p><p>Best regards,<br>{{app_name}} System</p>',
                'text_body' => 'Daily Report. Hello {{admin_name}}, Here is a summary of new appointments for {{date}}: Number of new appointments: {{appointment_count}}. {{appointment_list}}. Log in to the admin panel to see details. Best regards, {{app_name}} System',
                'blade_path' => 'emails.admin-daily-digest-en',
                'variables' => ['admin_name', 'date', 'appointment_count', 'appointment_list', 'app_name'],
                'active' => true,
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                [
                    'key' => $template['key'],
                    'language' => $template['language'],
                ],
                $template
            );
        }

        $this->command->info('✓ Email templates seeded successfully (18 templates)');
    }
}
