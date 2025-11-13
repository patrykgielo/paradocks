<?php

declare(strict_types=1);

namespace App\Jobs\Sms;

use App\Models\Appointment;
use App\Models\SmsSuppression;
use App\Services\Sms\SmsService;
use App\Support\Settings\SettingsManager;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * SendReminderSmsJob
 *
 * Scheduled Job (runs hourly)
 * Finds appointments 24h or 2h ahead and sends reminder SMS
 *
 * Business Logic:
 * - 24h reminder: sent_24h_reminder_sms = false, appointment_date between now+23h and now+25h
 * - 2h reminder: sent_2h_reminder_sms = false, appointment_date between now+1h and now+3h
 * - Only sends to confirmed appointments
 * - Respects SMS suppression list
 * - Checks if SMS is enabled in settings (globally + per reminder type)
 * - Marks reminders as sent to prevent duplicates
 *
 * Scheduled: Hourly via Laravel Scheduler
 * Queue: reminders
 */
class SendReminderSmsJob implements ShouldQueue, ShouldBeUnique
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
        return 'send-reminder-sms:' . now()->format('Y-m-d-H');
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService, SettingsManager $settings): void
    {
        Log::info('[SendReminderSmsJob] Starting reminder SMS processing');

        // Check if SMS is enabled globally
        $smsSettings = $settings->group('sms');
        if (!($smsSettings['enabled'] ?? true)) {
            Log::info('[SendReminderSmsJob] SMS globally disabled, skipping');
            return;
        }

        $stats = [
            '24h_sent' => 0,
            '2h_sent' => 0,
            '24h_skipped' => 0,
            '2h_skipped' => 0,
            '24h_failed' => 0,
            '2h_failed' => 0,
        ];

        // Send 24-hour reminders if enabled
        if ($smsSettings['send_reminder_24h'] ?? true) {
            $this->send24HourReminders($smsService, $stats);
        }

        // Send 2-hour reminders if enabled
        if ($smsSettings['send_reminder_2h'] ?? true) {
            $this->send2HourReminders($smsService, $stats);
        }

        Log::info('[SendReminderSmsJob] Completed reminder SMS processing', $stats);
    }

    /**
     * Send 24-hour reminders
     */
    private function send24HourReminders(SmsService $smsService, array &$stats): void
    {
        $appointments = Appointment::query()
            ->with(['customer', 'service'])
            ->where('status', 'confirmed')
            ->where('sent_24h_reminder_sms', false)
            ->whereNotNull('customer_phone') // Must have phone number
            ->whereBetween('appointment_date', [
                Carbon::now()->addHours(23),
                Carbon::now()->addHours(25),
            ])
            ->get();

        Log::info('[SendReminderSmsJob] Found {count} appointments for 24h reminders', [
            'count' => $appointments->count(),
        ]);

        foreach ($appointments as $appointment) {
            try {
                // Skip if phone is suppressed
                if (SmsSuppression::isSuppressed($appointment->customer_phone)) {
                    Log::warning('[SendReminderSmsJob] Skipping 24h reminder - phone suppressed', [
                        'appointment_id' => $appointment->id,
                        'phone' => $appointment->customer_phone,
                    ]);
                    $stats['24h_skipped']++;
                    continue;
                }

                // Get customer language preference
                $language = $appointment->customer?->preferred_language ?? 'pl';

                // Prepare SMS data
                $data = [
                    'customer_name' => $appointment->customer_name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_date->format('H:i'),
                    'service_name' => $appointment->service?->name ?? 'N/A',
                    'location_address' => $appointment->formatted_location ?? $appointment->location_address ?? '',
                    'app_name' => config('app.name'),
                    'contact_phone' => app(SettingsManager::class)->get('contact.phone', ''),
                ];

                // Send SMS
                $smsService->sendFromTemplate(
                    'appointment-reminder-24h',
                    $language,
                    $appointment->customer_phone,
                    $data,
                    [
                        'appointment_id' => $appointment->id,
                        'reminder_type' => '24h',
                    ]
                );

                // Mark as sent
                $appointment->update(['sent_24h_reminder_sms' => true]);

                $stats['24h_sent']++;

                Log::info('[SendReminderSmsJob] 24h reminder sent successfully', [
                    'appointment_id' => $appointment->id,
                    'phone' => $appointment->customer_phone,
                ]);
            } catch (\Exception $e) {
                $stats['24h_failed']++;

                Log::error('[SendReminderSmsJob] Failed to send 24h reminder', [
                    'appointment_id' => $appointment->id,
                    'phone' => $appointment->customer_phone,
                    'error' => $e->getMessage(),
                ]);

                // Don't mark as sent - will retry next hour
            }
        }
    }

    /**
     * Send 2-hour reminders
     */
    private function send2HourReminders(SmsService $smsService, array &$stats): void
    {
        $appointments = Appointment::query()
            ->with(['customer', 'service'])
            ->where('status', 'confirmed')
            ->where('sent_2h_reminder_sms', false)
            ->whereNotNull('customer_phone') // Must have phone number
            ->whereBetween('appointment_date', [
                Carbon::now()->addHours(1),
                Carbon::now()->addHours(3),
            ])
            ->get();

        Log::info('[SendReminderSmsJob] Found {count} appointments for 2h reminders', [
            'count' => $appointments->count(),
        ]);

        foreach ($appointments as $appointment) {
            try {
                // Skip if phone is suppressed
                if (SmsSuppression::isSuppressed($appointment->customer_phone)) {
                    Log::warning('[SendReminderSmsJob] Skipping 2h reminder - phone suppressed', [
                        'appointment_id' => $appointment->id,
                        'phone' => $appointment->customer_phone,
                    ]);
                    $stats['2h_skipped']++;
                    continue;
                }

                // Get customer language preference
                $language = $appointment->customer?->preferred_language ?? 'pl';

                // Prepare SMS data
                $data = [
                    'customer_name' => $appointment->customer_name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_date->format('H:i'),
                    'service_name' => $appointment->service?->name ?? 'N/A',
                    'location_address' => $appointment->formatted_location ?? $appointment->location_address ?? '',
                    'app_name' => config('app.name'),
                    'contact_phone' => app(SettingsManager::class)->get('contact.phone', ''),
                ];

                // Send SMS
                $smsService->sendFromTemplate(
                    'appointment-reminder-2h',
                    $language,
                    $appointment->customer_phone,
                    $data,
                    [
                        'appointment_id' => $appointment->id,
                        'reminder_type' => '2h',
                    ]
                );

                // Mark as sent
                $appointment->update(['sent_2h_reminder_sms' => true]);

                $stats['2h_sent']++;

                Log::info('[SendReminderSmsJob] 2h reminder sent successfully', [
                    'appointment_id' => $appointment->id,
                    'phone' => $appointment->customer_phone,
                ]);
            } catch (\Exception $e) {
                $stats['2h_failed']++;

                Log::error('[SendReminderSmsJob] Failed to send 2h reminder', [
                    'appointment_id' => $appointment->id,
                    'phone' => $appointment->customer_phone,
                    'error' => $e->getMessage(),
                ]);

                // Don't mark as sent - will retry next hour
            }
        }
    }
}
