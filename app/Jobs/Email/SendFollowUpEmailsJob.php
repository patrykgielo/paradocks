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
 * SendFollowUpEmailsJob
 *
 * Scheduled Job (runs hourly)
 * Finds completed appointments from 24h ago and sends follow-up emails
 *
 * Business Logic:
 * - Sends to appointments with status = 'completed'
 * - Only appointments completed 23-25 hours ago
 * - sent_followup = false (not already sent)
 * - Respects email suppression list
 * - Marks as sent to prevent duplicates
 *
 * Scheduled: Hourly via Laravel Scheduler
 * Queue: emails
 */
class SendFollowUpEmailsJob implements ShouldBeUnique, ShouldQueue
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
        $this->onQueue('emails');
    }

    /**
     * Get the unique ID for the job (ensures only one instance runs at a time).
     */
    public function uniqueId(): string
    {
        return 'send-followup-emails:'.now()->format('Y-m-d-H');
    }

    /**
     * Execute the job.
     */
    public function handle(EmailService $emailService): void
    {
        Log::info('[SendFollowUpEmailsJob] Starting follow-up email processing');

        $stats = [
            'sent' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        // Find completed appointments from 24h ago (23-25h window)
        $appointments = Appointment::query()
            ->with(['customer', 'service'])
            ->where('status', 'completed')
            ->where('sent_followup', false)
            ->whereBetween('appointment_date', [
                Carbon::now()->subHours(25),
                Carbon::now()->subHours(23),
            ])
            ->get();

        Log::info('[SendFollowUpEmailsJob] Found {count} completed appointments for follow-up', [
            'count' => $appointments->count(),
        ]);

        foreach ($appointments as $appointment) {
            try {
                // Skip if email is suppressed
                if (EmailSuppression::isSuppressed($appointment->customer_email)) {
                    Log::warning('[SendFollowUpEmailsJob] Skipping follow-up - email suppressed', [
                        'appointment_id' => $appointment->id,
                        'email' => $appointment->customer_email,
                    ]);
                    $stats['skipped']++;

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
                    'vehicle_display' => $appointment->vehicle_display,
                    'app_name' => config('app.name'),
                    'contact_email' => config('mail.from.address'),
                    'contact_phone' => app(\App\Support\Settings\SettingsManager::class)->get('contact.phone', ''),
                    'booking_url' => config('app.url').'/booking',
                    'review_url' => config('app.url').'/review',
                ];

                // Send email
                $emailService->sendFromTemplate(
                    'appointment-followup',
                    $language,
                    $appointment->customer_email,
                    $data,
                    [
                        'appointment_id' => $appointment->id,
                        'followup_type' => '24h',
                    ]
                );

                // Mark as sent
                $appointment->update(['sent_followup' => true]);

                $stats['sent']++;

                Log::info('[SendFollowUpEmailsJob] Follow-up email sent successfully', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->customer_email,
                ]);
            } catch (\Exception $e) {
                $stats['failed']++;

                Log::error('[SendFollowUpEmailsJob] Failed to send follow-up email', [
                    'appointment_id' => $appointment->id,
                    'email' => $appointment->customer_email,
                    'error' => $e->getMessage(),
                ]);

                // Don't mark as sent - will retry next hour
            }
        }

        Log::info('[SendFollowUpEmailsJob] Completed follow-up email processing', $stats);
    }
}
