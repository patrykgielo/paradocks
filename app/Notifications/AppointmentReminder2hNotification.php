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
 * Appointment Reminder 2 Hours Notification
 *
 * Sent 2 hours before an appointment as a final reminder.
 * Dispatched by scheduler job.
 */
class AppointmentReminder2hNotification extends Notification implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(
        public Appointment $appointment
    ) {
        $this->onQueue('reminders');
    }

    /**
     * Get the unique ID for the notification.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'appointment-reminder-2h:' . $this->appointment->id;
    }

    /**
     * Get the number of seconds the unique lock should be maintained.
     *
     * @return int
     */
    public function uniqueFor(): int
    {
        return 3600; // 1 hour (prevent duplicate reminders)
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $emailService = app(EmailService::class);
        $language = $notifiable->preferred_language ?? 'pl';

        // Load relationships
        $appointment = $this->appointment->load(['service', 'customer']);

        try {
            $emailSend = $emailService->sendFromTemplate(
                'appointment-reminder-2h',
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
                    'notification' => 'AppointmentReminder2hNotification',
                ]
            );

            return (new MailMessage)
                ->subject($emailSend->subject)
                ->line('2-hour appointment reminder sent successfully via EmailService.');
        } catch (\Exception $e) {
            Log::error('AppointmentReminder2hNotification failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
