<?php

namespace App\Notifications;

use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class AdminCreatedUserNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(public User $user)
    {
        $this->onQueue('emails');
    }

    public function uniqueId(): string
    {
        return 'admin-created-user:'.$this->user->id;
    }

    public function uniqueFor(): int
    {
        return 300; // 5 minutes
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $emailService = app(EmailService::class);
        $language = $notifiable->preferred_language ?? 'pl';

        try {
            $setupUrl = url(route('password.setup', ['token' => $notifiable->password_setup_token]));

            $emailSend = $emailService->sendFromTemplate(
                'admin-user-created',
                $language,
                $notifiable->email,
                [
                    'user_name' => $notifiable->name,
                    'app_name' => config('app.name'),
                    'user_email' => $notifiable->email,
                    'setup_url' => $setupUrl,
                    'expires_at' => $notifiable->password_setup_expires_at->format('d.m.Y H:i'),
                ],
                [
                    'user_id' => $notifiable->id,
                    'notification' => 'AdminCreatedUserNotification',
                ]
            );

            return (new MailMessage)
                ->subject($emailSend->subject)
                ->line('Email sent successfully via EmailService.');
        } catch (\Exception $e) {
            Log::error('AdminCreatedUserNotification failed', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
