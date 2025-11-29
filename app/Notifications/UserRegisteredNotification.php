<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * User Registered Notification
 *
 * Sent when a new user registers on the platform.
 * Queued with uniqueness to prevent duplicate welcome emails.
 */
class UserRegisteredNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public User $user
    ) {
        $this->onQueue('emails');
    }

    /**
     * Get the unique ID for the notification.
     */
    public function uniqueId(): string
    {
        return 'user-registered:'.$this->user->id;
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

        try {
            $emailSend = $emailService->sendFromTemplate(
                'user-registered',
                $language,
                $notifiable->email,
                [
                    'user_name' => $notifiable->name,
                    'app_name' => config('app.name'),
                    'user_email' => $notifiable->email,
                ],
                [
                    'user_id' => $notifiable->id,
                    'notification' => 'UserRegisteredNotification',
                ]
            );

            // Return MailMessage (required by Laravel Notifications)
            return (new MailMessage)
                ->subject($emailSend->subject)
                ->line('Email sent successfully via EmailService.');
        } catch (\Exception $e) {
            // Log error and re-throw for queue retry
            Log::error('UserRegisteredNotification failed', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
