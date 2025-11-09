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
 * Password Reset Notification
 *
 * Sent when a user requests a password reset.
 * Contains secure token link for resetting password.
 */
class PasswordResetNotification extends Notification implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\User $user
     * @param string $token Password reset token
     */
    public function __construct(
        public User $user,
        public string $token
    ) {
        $this->onQueue('emails');
    }

    /**
     * Get the unique ID for the notification.
     *
     * @return string
     */
    public function uniqueId(): string
    {
        return 'password-reset:' . $this->user->id . ':' . substr($this->token, 0, 8);
    }

    /**
     * Get the number of seconds the unique lock should be maintained.
     *
     * @return int
     */
    public function uniqueFor(): int
    {
        return 300; // 5 minutes
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

        // Build password reset URL
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], false));

        try {
            $emailSend = $emailService->sendFromTemplate(
                'password-reset',
                $language,
                $notifiable->email,
                [
                    'user_name' => $notifiable->name,
                    'reset_url' => $resetUrl,
                    'token' => $this->token,
                ],
                [
                    'user_id' => $notifiable->id,
                    'notification' => 'PasswordResetNotification',
                ]
            );

            return (new MailMessage)
                ->subject($emailSend->subject)
                ->line('Password reset email sent successfully via EmailService.');
        } catch (\Exception $e) {
            Log::error('PasswordResetNotification failed', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
