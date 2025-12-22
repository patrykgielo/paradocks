<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\Coupon;
use App\Services\Email\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Coupon Rewarded Notification
 *
 * Sent to customer after completing an appointment when they earn a reward coupon.
 * Queued on 'high' priority for timely delivery.
 */
class CouponRewardedNotification extends Notification implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Coupon $coupon,
        public Appointment $appointment
    ) {
        $this->onQueue('high'); // High priority for reward notifications
    }

    /**
     * Get the unique ID for the notification.
     */
    public function uniqueId(): string
    {
        return 'coupon-rewarded:'.$this->coupon->id.':'.$this->appointment->id;
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
        $coupon = $this->coupon;

        try {
            $emailSend = $emailService->sendFromTemplate(
                'coupon-rewarded',
                [
                    'customer' => $notifiable,
                    'coupon' => $coupon,
                    'appointment' => $appointment,
                ],
                $language
            );

            if (! $emailSend->sent) {
                Log::error('Failed to send coupon rewarded email', [
                    'customer_id' => $notifiable->id,
                    'coupon_id' => $coupon->id,
                    'appointment_id' => $appointment->id,
                    'error' => $emailSend->error,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Exception sending coupon rewarded email', [
                'customer_id' => $notifiable->id,
                'coupon_id' => $coupon->id,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // Return fallback (won't be used if EmailService succeeds)
        return $this->buildFallbackEmail($language);
    }

    /**
     * Build fallback email if template system fails
     */
    private function buildFallbackEmail(string $language): MailMessage
    {
        if ($language === 'en') {
            return (new MailMessage)
                ->subject('Congratulations! You earned a discount coupon')
                ->line('Thank you for choosing ParaDocks!')
                ->line('As a reward for completing your appointment, we are pleased to offer you a discount coupon for your next visit.')
                ->line('Your discount code: **'.$this->coupon->code.'**')
                ->line('Discount: '.$this->coupon->formatted_discount)
                ->line('Valid until: '.$this->coupon->valid_until->format('d.m.Y'))
                ->line('Use this code when booking your next appointment to receive your discount.')
                ->action('Book Appointment', url('/booking'))
                ->line('We look forward to seeing you again!');
        }

        // Polish (default)
        return (new MailMessage)
            ->subject('Gratulacje! Otrzymałeś kod rabatowy')
            ->line('Dziękujemy za wybranie ParaDocks!')
            ->line('W nagrodę za ukończoną wizytę, mamy przyjemność zaoferować Ci kod rabatowy na następną wizytę.')
            ->line('Twój kod rabatowy: **'.$this->coupon->code.'**')
            ->line('Rabat: '.$this->coupon->formatted_discount)
            ->line('Ważny do: '.$this->coupon->valid_until->format('d.m.Y'))
            ->line('Użyj tego kodu przy rezerwacji kolejnej wizyty, aby otrzymać rabat.')
            ->action('Zarezerwuj wizytę', url('/booking'))
            ->line('Czekamy na Ciebie ponownie!');
    }
}
