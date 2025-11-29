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
 * SendFollowUpSmsJob
 *
 * Scheduled Job (runs hourly)
 * Finds completed appointments from 24h ago and sends follow-up SMS
 *
 * Business Logic:
 * - Sends to appointments with status = 'completed'
 * - Only appointments completed 23-25 hours ago
 * - sent_followup_sms = false (not already sent)
 * - Respects SMS suppression list
 * - Checks if follow-up SMS is enabled in settings
 * - Marks as sent to prevent duplicates
 *
 * Scheduled: Hourly via Laravel Scheduler
 * Queue: sms
 */
class SendFollowUpSmsJob implements ShouldBeUnique, ShouldQueue
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
        $this->onQueue('sms');
    }

    /**
     * Get the unique ID for the job (ensures only one instance runs at a time).
     */
    public function uniqueId(): string
    {
        return 'send-followup-sms:'.now()->format('Y-m-d-H');
    }

    /**
     * Execute the job.
     */
    public function handle(SmsService $smsService, SettingsManager $settings): void
    {
        Log::info('[SendFollowUpSmsJob] Starting follow-up SMS processing');

        // Check if SMS is enabled globally
        $smsSettings = $settings->group('sms');
        if (! ($smsSettings['enabled'] ?? true)) {
            Log::info('[SendFollowUpSmsJob] SMS globally disabled, skipping');

            return;
        }

        // Check if follow-up SMS is enabled
        if (! ($smsSettings['send_follow_up'] ?? true)) {
            Log::info('[SendFollowUpSmsJob] Follow-up SMS disabled, skipping');

            return;
        }

        $stats = [
            'sent' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        // Find completed appointments from 24h ago (23-25h window)
        $appointments = Appointment::query()
            ->with(['customer', 'service'])
            ->where('status', 'completed')
            ->where('sent_followup_sms', false)
            ->whereNotNull('customer_phone') // Must have phone number
            ->whereBetween('appointment_date', [
                Carbon::now()->subHours(25),
                Carbon::now()->subHours(23),
            ])
            ->get();

        Log::info('[SendFollowUpSmsJob] Found {count} completed appointments for follow-up', [
            'count' => $appointments->count(),
        ]);

        foreach ($appointments as $appointment) {
            try {
                // Skip if phone is suppressed
                if (SmsSuppression::isSuppressed($appointment->customer_phone)) {
                    Log::warning('[SendFollowUpSmsJob] Skipping follow-up - phone suppressed', [
                        'appointment_id' => $appointment->id,
                        'phone' => $appointment->customer_phone,
                    ]);
                    $stats['skipped']++;

                    continue;
                }

                // Get customer language preference
                $language = $appointment->customer?->preferred_language ?? 'pl';

                // Prepare SMS data
                $data = [
                    'customer_name' => $appointment->customer_name,
                    'service_name' => $appointment->service?->name ?? 'N/A',
                    'app_name' => config('app.name'),
                    'contact_phone' => $settings->get('contact.phone', ''),
                ];

                // Send SMS
                $smsService->sendFromTemplate(
                    'appointment-follow-up',
                    $language,
                    $appointment->customer_phone,
                    $data,
                    [
                        'appointment_id' => $appointment->id,
                        'type' => 'follow-up',
                    ]
                );

                // Mark as sent
                $appointment->update(['sent_followup_sms' => true]);

                $stats['sent']++;

                Log::info('[SendFollowUpSmsJob] Follow-up SMS sent successfully', [
                    'appointment_id' => $appointment->id,
                    'phone' => $appointment->customer_phone,
                ]);
            } catch (\Exception $e) {
                $stats['failed']++;

                Log::error('[SendFollowUpSmsJob] Failed to send follow-up SMS', [
                    'appointment_id' => $appointment->id,
                    'phone' => $appointment->customer_phone,
                    'error' => $e->getMessage(),
                ]);

                // Don't mark as sent - will retry next hour
            }
        }

        Log::info('[SendFollowUpSmsJob] Completed follow-up SMS processing', $stats);
    }
}
