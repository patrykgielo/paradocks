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
 * Appointment Reminder 24 Hours Notification
 *
 * Sent 24 hours before an appointment as a reminder.
 * Dispatched by scheduler job.
 */
class AppointmentReminder24hNotification extends Notification implements ShouldBeUnique, ShouldQueue
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
        return 'appointment-reminder-24h:'.$this->appointment->id;
    }

    /**
     * Get the number of seconds the unique lock should be maintained.
     */
    public function uniqueFor(): int
    {
        return 3600; // 1 hour (prevent duplicate reminders)
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
                'appointment-reminder-24h',
                $language,
                $notifiable->email,
                [
                    'customer_name' => $appointment->customer->name,
                    'service_name' => $appointment->service->name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->start_time,
                ],
                [
                    'appointment_id' => $appointment->id,
                    'notification' => 'AppointmentReminder24hNotification',
                ]
            );

            return (new MailMessage)
                ->subject($emailSend->subject)
                ->line('24-hour appointment reminder sent successfully via EmailService.');
        } catch (\Exception $e) {
            Log::error('AppointmentReminder24hNotification failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
