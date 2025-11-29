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
 * Appointment Cancelled Notification
 *
 * Sent when an appointment is cancelled.
 * Includes cancellation reason if provided.
 */
class AppointmentCancelledNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  string|null  $reason  Cancellation reason
     */
    public function __construct(
        public Appointment $appointment,
        public ?string $reason = null
    ) {
        $this->onQueue('emails');
    }

    /**
     * Get the unique ID for the notification.
     */
    public function uniqueId(): string
    {
        return 'appointment-cancelled:'.$this->appointment->id;
    }

    /**
     * Get the number of seconds the unique lock should be maintained.
     */
    public function uniqueFor(): int
    {
        return 300; // 5 minutes
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

        try {
            $emailSend = $emailService->sendFromTemplate(
                'appointment-cancelled',
                $language,
                $notifiable->email,
                [
                    'customer_name' => $appointment->customer->name,
                    'service_name' => $appointment->service->name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'reason' => $this->reason ?? 'Nie podano powodu',
                ],
                [
                    'appointment_id' => $appointment->id,
                    'notification' => 'AppointmentCancelledNotification',
                ]
            );

            return (new MailMessage)
                ->subject($emailSend->subject)
                ->line('Appointment cancellation notification sent successfully via EmailService.');
        } catch (\Exception $e) {
            Log::error('AppointmentCancelledNotification failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
