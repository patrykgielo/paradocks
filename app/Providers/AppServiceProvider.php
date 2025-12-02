<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\AdminCreatedUser;
use App\Events\AppointmentCancelled;
use App\Events\AppointmentConfirmed;
use App\Events\AppointmentCreated;
use App\Events\AppointmentFollowUp;
use App\Events\AppointmentReminder24h;
use App\Events\AppointmentReminder2h;
use App\Events\AppointmentRescheduled;
use App\Events\PasswordResetRequested;
use App\Events\UserRegistered;
use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AdminCreatedUserNotification;
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
use App\Services\MaintenanceService;
use App\Services\Sms\SmsApiGateway;
use App\Services\Sms\SmsGatewayInterface;
use App\Services\Sms\SmsService;
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

        // Register MaintenanceService as singleton
        $this->app->singleton(MaintenanceService::class);

        // Bind EmailGateway interface to SMTP implementation
        $this->app->bind(EmailGatewayInterface::class, SmtpMailer::class);

        // Register EmailService as singleton
        $this->app->singleton(EmailService::class);

        // Bind SmsGateway interface to SMSAPI implementation
        $this->app->bind(SmsGatewayInterface::class, SmsApiGateway::class);

        // Register SmsService as singleton
        $this->app->singleton(SmsService::class);
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
            if (! empty($emailSettings['smtp_host'])) {
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

        // Admin Created User (password setup email)
        Event::listen(AdminCreatedUser::class, function (AdminCreatedUser $event) {
            $event->user->notify(new AdminCreatedUserNotification($event->user));
        });

        // Appointment Created
        Event::listen(AppointmentCreated::class, function (AppointmentCreated $event) {
            // Notify customer
            $event->appointment->customer->notify(
                new AppointmentCreatedNotification($event->appointment, 'customer')
            );
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

        // ========== SMS NOTIFICATIONS ==========

        // Send booking confirmation SMS when customer creates appointment
        Event::listen(AppointmentCreated::class, function (AppointmentCreated $event) {
            $this->sendSmsNotification(
                'booking_confirmation',
                $event->appointment,
                'send_booking_confirmation'
            );
        });

        // Send admin confirmation SMS when admin confirms appointment
        Event::listen(AppointmentConfirmed::class, function (AppointmentConfirmed $event) {
            $this->sendSmsNotification(
                'admin_confirmation',
                $event->appointment,
                'send_admin_confirmation'
            );
        });
    }

    /**
     * Send SMS notification for appointment event.
     *
     * @param  string  $templateKey  Template key (e.g., 'booking_confirmation')
     * @param  \App\Models\Appointment  $appointment  The appointment
     * @param  string  $settingKey  Setting key to check if enabled (e.g., 'send_booking_confirmation')
     */
    private function sendSmsNotification(string $templateKey, $appointment, string $settingKey): void
    {
        try {
            $smsService = app(SmsService::class);
            $settingsManager = app(SettingsManager::class);
            $smsSettings = $settingsManager->group('sms');

            // Check if SMS globally enabled
            if (! ($smsSettings['enabled'] ?? true)) {
                return;
            }

            // Check if specific notification type is enabled
            if (! ($smsSettings[$settingKey] ?? true)) {
                return;
            }

            // Get customer phone number
            $customerPhone = $appointment->customer->phone ?? null;
            if (! $customerPhone) {
                \Log::warning('Cannot send SMS notification: customer has no phone number', [
                    'appointment_id' => $appointment->id,
                    'customer_id' => $appointment->customer->id,
                    'template_key' => $templateKey,
                ]);

                return;
            }

            // Prepare template data
            $data = [
                'customer_name' => $appointment->customer->name,
                'service_name' => $appointment->service->name ?? 'N/A',
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'start_time' => $appointment->start_time->format('H:i'),
                'location' => $appointment->location_address ?? 'N/A',
            ];

            // Send SMS
            $smsService->sendFromTemplate(
                $templateKey,
                'pl', // Default to Polish
                $customerPhone,
                $data,
                ['appointment_id' => $appointment->id]
            );

            \Log::info('SMS notification sent successfully', [
                'template_key' => $templateKey,
                'appointment_id' => $appointment->id,
                'phone' => substr($customerPhone, 0, 3).'***', // Masked for privacy
            ]);
        } catch (\Exception $e) {
            // Log error but don't throw - SMS failure shouldn't block appointment flow
            \Log::error('Failed to send SMS notification', [
                'template_key' => $templateKey,
                'appointment_id' => $appointment->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
