<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Appointment;
use App\Services\Email\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Appointment Follow-Up Notification
 *
 * Sent 24 hours after a completed appointment.
 * Includes review request and feedback link.
 */
class AppointmentFollowUpNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Appointment $appointment
    ) {
        $this->onQueue('reminders');
    }

    /**
     * Get the unique ID for the notification.
     */
    public function uniqueId(): string
    {
        return 'appointment-followup:'.$this->appointment->id;
    }

    /**
     * Get the number of seconds the unique lock should be maintained.
     */
    public function uniqueFor(): int
    {
        return 86400; // 24 hours (prevent duplicate follow-ups)
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     */
    public function toMail(object $notifiable): MailMessage
    {
        $emailService = app(EmailService::class);
        $language = $notifiable->preferred_language ?? 'pl';

        // Load relationships
        $appointment = $this->appointment->load(['service', 'customer']);

        // Build review URL (placeholder - adjust based on actual review system)
        $reviewUrl = url('/reviews/create?appointment_id='.$appointment->id);

        try {
            $emailSend = $emailService->sendFromTemplate(
                'appointment-followup',
                $language,
                $notifiable->email,
                [
                    'customer_name' => $appointment->customer->name,
                    'service_name' => $appointment->service->name,
                    'review_url' => $reviewUrl,
                ],
                [
                    'appointment_id' => $appointment->id,
                    'notification' => 'AppointmentFollowUpNotification',
                ]
            );

            return (new MailMessage)
                ->subject($emailSend->subject)
                ->line('Appointment follow-up email sent successfully via EmailService.');
        } catch (\Exception $e) {
            Log::error('AppointmentFollowUpNotification failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
