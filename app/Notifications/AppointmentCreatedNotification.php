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
 * Appointment Created Notification
 *
 * Sent when a new appointment is created.
 * Different templates for customer and admin recipients.
 */
class AppointmentCreatedNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  string  $recipientType  'customer' or 'admin'
     */
    public function __construct(
        public Appointment $appointment,
        public string $recipientType = 'customer'
    ) {
        $this->onQueue('emails');
    }

    /**
     * Get the unique ID for the notification.
     */
    public function uniqueId(): string
    {
        return 'appointment-created:'.$this->appointment->id.':'.$this->recipientType;
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
                'appointment-created',
                $language,
                $notifiable->email,
                [
                    'customer_name' => $appointment->customer->name,
                    'service_name' => $appointment->service->name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->start_time,
                    'location_address' => $appointment->location_address ?? 'N/A',
                    'recipient_type' => $this->recipientType,
                ],
                [
                    'appointment_id' => $appointment->id,
                    'recipient_type' => $this->recipientType,
                    'notification' => 'AppointmentCreatedNotification',
                ]
            );

            return (new MailMessage)
                ->subject($emailSend->subject)
                ->line('Appointment confirmation sent successfully via EmailService.');
        } catch (\Exception $e) {
            Log::error('AppointmentCreatedNotification failed', [
                'appointment_id' => $appointment->id,
                'recipient_type' => $this->recipientType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
