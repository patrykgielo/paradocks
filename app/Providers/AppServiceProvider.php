<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\AppointmentCancelled;
use App\Events\AppointmentCreated;
use App\Events\AppointmentFollowUp;
use App\Events\AppointmentReminder24h;
use App\Events\AppointmentReminder2h;
use App\Events\AppointmentRescheduled;
use App\Events\PasswordResetRequested;
use App\Events\UserRegistered;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentCreatedNotification;
use App\Notifications\AppointmentFollowUpNotification;
use App\Notifications\AppointmentReminder24hNotification;
use App\Notifications\AppointmentReminder2hNotification;
use App\Notifications\AppointmentRescheduledNotification;
use App\Notifications\PasswordResetNotification;
use App\Notifications\UserRegisteredNotification;
use App\Observers\AppointmentObserver;
use App\Services\Email\EmailGatewayInterface;
use App\Services\Email\EmailService;
use App\Services\Email\SmtpMailer;
use App\Support\Settings\SettingsManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register SettingsManager as singleton
        $this->app->singleton(SettingsManager::class);

        // Bind EmailGateway interface to SMTP implementation
        $this->app->bind(EmailGatewayInterface::class, SmtpMailer::class);

        // Register EmailService as singleton
        $this->app->singleton(EmailService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);

        // Override mail configuration with database settings
        $this->configureMailFromDatabase();

        // Register event listeners for email notifications
        $this->registerEventListeners();
    }

    /**
     * Override runtime mail configuration with settings from database.
     *
     * This allows dynamic SMTP configuration without modifying .env file.
     * Only applies if smtp_host is set in database settings.
     */
    private function configureMailFromDatabase(): void
    {
        try {
            $settingsManager = app(SettingsManager::class);
            $emailSettings = $settingsManager->group('email');

            // Only override if SMTP host is configured
            if (!empty($emailSettings['smtp_host'])) {
                config([
                    'mail.mailers.smtp.host' => $emailSettings['smtp_host'],
                    'mail.mailers.smtp.port' => $emailSettings['smtp_port'] ?? 587,
                    'mail.mailers.smtp.encryption' => $emailSettings['smtp_encryption'] ?? 'tls',
                    'mail.mailers.smtp.username' => $emailSettings['smtp_username'] ?? null,
                    'mail.mailers.smtp.password' => $emailSettings['smtp_password'] ?? null,
                    'mail.from.address' => $emailSettings['from_address'] ?? config('mail.from.address'),
                    'mail.from.name' => $emailSettings['from_name'] ?? config('mail.from.name'),
                ]);
            }
        } catch (\Exception $e) {
            // Fail silently during migrations or if settings table doesn't exist yet
            // This prevents errors during initial setup
        }
    }

    /**
     * Register event listeners for email notifications.
     *
     * Maps domain events to notification dispatchers.
     */
    private function registerEventListeners(): void
    {
        // User Registration
        Event::listen(UserRegistered::class, function (UserRegistered $event) {
            $event->user->notify(new UserRegisteredNotification($event->user));
        });

        // Password Reset
        Event::listen(PasswordResetRequested::class, function (PasswordResetRequested $event) {
            $event->user->notify(new PasswordResetNotification($event->user, $event->token));
        });

        // Appointment Created
        Event::listen(AppointmentCreated::class, function (AppointmentCreated $event) {
            // Notify customer
            $event->appointment->customer->notify(
                new AppointmentCreatedNotification($event->appointment, 'customer')
            );

            // Notify admins with 'manage appointments' permission
            User::permission('manage appointments')->each(function ($admin) use ($event) {
                $admin->notify(new AppointmentCreatedNotification($event->appointment, 'admin'));
            });
        });

        // Appointment Rescheduled
        Event::listen(AppointmentRescheduled::class, function (AppointmentRescheduled $event) {
            $event->appointment->customer->notify(
                new AppointmentRescheduledNotification(
                    $event->appointment,
                    $event->oldDate,
                    $event->newDate,
                    'staff'
                )
            );
        });

        // Appointment Cancelled
        Event::listen(AppointmentCancelled::class, function (AppointmentCancelled $event) {
            $event->appointment->customer->notify(
                new AppointmentCancelledNotification($event->appointment, $event->reason)
            );
        });

        // Appointment Reminder 24h
        Event::listen(AppointmentReminder24h::class, function (AppointmentReminder24h $event) {
            $event->appointment->customer->notify(
                new AppointmentReminder24hNotification($event->appointment)
            );
        });

        // Appointment Reminder 2h
        Event::listen(AppointmentReminder2h::class, function (AppointmentReminder2h $event) {
            $event->appointment->customer->notify(
                new AppointmentReminder2hNotification($event->appointment)
            );
        });

        // Appointment Follow-Up
        Event::listen(AppointmentFollowUp::class, function (AppointmentFollowUp $event) {
            $event->appointment->customer->notify(
                new AppointmentFollowUpNotification($event->appointment)
            );
        });
    }
}

