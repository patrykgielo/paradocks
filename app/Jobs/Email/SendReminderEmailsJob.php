<?php

declare(strict_types=1);

namespace App\Jobs\Email;

use App\Models\Appointment;
use App\Models\EmailSuppression;
use App\Services\Email\EmailService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendReminderEmailsJob
 *
 * Scheduled Job (runs hourly)
 * Finds appointments 24h or 2h ahead and sends reminder emails
 *
 * Business Logic:
 * - 24h reminder: sent_24h_reminder = false, appointment_date between now+23h and now+25h
 * - 2h reminder: sent_2h_reminder = false, appointment_date between now+1h and now+3h
 * - Only sends to confirmed/pending appointments
 * - Respects email suppression list
 * - Marks reminders as sent to prevent duplicates
 *
 * Scheduled: Hourly via Laravel Scheduler
 * Queue: reminders
 */
class SendReminderEmailsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('reminders');
    }

    /**
     * Get the unique ID for the job (ensures only one instance runs at a time).
     */
    public function uniqueId(): string
    {
        return 'send-reminder-emails:'.now()->format('Y-m-d-H');
    }

    /**
     * Execute the job.
     */
    public function handle(EmailService $emailService): void
    {
        Log::info('[SendReminderEmailsJob] Starting reminder email processing');

        $stats = [
            '24h_sent' => 0,
            '2h_sent' => 0,
            '24h_skipped' => 0,
            '2h_skipped' => 0,
            '24h_failed' => 0,
            '2h_failed' => 0,
        ];

        // Send 24-hour reminders
        $this->send24HourReminders($emailService, $stats);

        // Send 2-hour reminders
        $this->send2HourReminders($emailService, $stats);

        Log::info('[SendReminderEmailsJob] Completed reminder email processing', $stats);
    }

    /**
     * Send 24-hour reminders
     */
    private function send24HourReminders(EmailService $emailService, array &$stats): void
    {
        $appointments = Appointment::query()
            ->with(['customer', 'service'])
            ->where('status', 'confirmed')
            ->where('sent_24h_reminder', false)
            ->whereBetween('appointment_date', [
                Carbon::now()->addHours(23),
                Carbon::now()->addHours(25),
            ])
            ->get();

        Log::info('[SendReminderEmailsJob] Found {count} appointments for 24h reminders', [
            'count' => $appointments->count(),
        ]);

        foreach ($appointments as $appointment) {
            try {
                // Skip if email is suppressed
                if (EmailSuppression::isSuppressed($appointment->customer_email)) {
                    Log::warning('[SendReminderEmailsJob] Skipping 24h reminder - email suppressed', [
                        'appointment_id' => $appointment->id,
                        'email' => $appointment->customer_email,
                    ]);
                    $stats['24h_skipped']++;

                    continue;
                }

                // Get customer language preference
                $language = $appointment->customer?->preferred_language ?? 'pl';

                // Prepare email data
                $data = [
                    'customer_name' => $appointment->customer_name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_date->format('H:i'),
                    'service_name' => $appointment->service?->name ?? 'N/A',
                    'location_address' => $appointment->formatted_location,
                    'app_name' => config('app.name'),
                    'contact_email' => config('mail.from.address'),
                    'contact_phone' => app(\App\Support\Settings\SettingsManager::class)->get('contact.phone', ''),
                ];

                // Send email
                $emailService->sendFromTemplate(
                    'appointment-reminder-24h',
                    $language,
                    $appointment->customer_email,
                    $data,
                    [
                        'appointment_id' => $appointment->id,
                        'reminder_type' => '24h',
                    ]
                );

                // Mark as sent
                $appointment->update(['sent_24h_reminder' => true]);

                $stats['24h_sent']++;

                Log::info('[SendReminderEmailsJob] 24h reminder sent successfully', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->customer_email,
                ]);
            } catch (\Exception $e) {
                $stats['24h_failed']++;

                Log::error('[SendReminderEmailsJob] Failed to send 24h reminder', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->customer_email,
                    'error' => $e->getMessage(),
                ]);

                // Don't mark as sent - will retry next hour
            }
        }
    }

    /**
     * Send 2-hour reminders
     */
    private function send2HourReminders(EmailService $emailService, array &$stats): void
    {
        $appointments = Appointment::query()
            ->with(['customer', 'service'])
            ->where('status', 'confirmed')
            ->where('sent_2h_reminder', false)
            ->whereBetween('appointment_date', [
                Carbon::now()->addHours(1),
                Carbon::now()->addHours(3),
            ])
            ->get();

        Log::info('[SendReminderEmailsJob] Found {count} appointments for 2h reminders', [
            'count' => $appointments->count(),
        ]);

        foreach ($appointments as $appointment) {
            try {
                // Skip if email is suppressed
                if (EmailSuppression::isSuppressed($appointment->customer_email)) {
                    Log::warning('[SendReminderEmailsJob] Skipping 2h reminder - email suppressed', [
                        'appointment_id' => $appointment->id,
                        'email' => $appointment->customer_email,
                    ]);
                    $stats['2h_skipped']++;

                    continue;
                }

                // Get customer language preference
                $language = $appointment->customer?->preferred_language ?? 'pl';

                // Prepare email data
                $data = [
                    'customer_name' => $appointment->customer_name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_date->format('H:i'),
                    'service_name' => $appointment->service?->name ?? 'N/A',
                    'location_address' => $appointment->formatted_location,
                    'app_name' => config('app.name'),
                    'contact_email' => config('mail.from.address'),
                    'contact_phone' => app(\App\Support\Settings\SettingsManager::class)->get('contact.phone', ''),
                ];

                // Send email
                $emailService->sendFromTemplate(
                    'appointment-reminder-2h',
                    $language,
                    $appointment->customer_email,
                    $data,
                    [
                        'appointment_id' => $appointment->id,
                        'reminder_type' => '2h',
                    ]
                );

                // Mark as sent
                $appointment->update(['sent_2h_reminder' => true]);

                $stats['2h_sent']++;

                Log::info('[SendReminderEmailsJob] 2h reminder sent successfully', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->customer_email,
                ]);
            } catch (\Exception $e) {
                $stats['2h_failed']++;

                Log::error('[SendReminderEmailsJob] Failed to send 2h reminder', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->customer_email,
                    'error' => $e->getMessage(),
                ]);

                // Don't mark as sent - will retry next hour
            }
        }
    }
}
