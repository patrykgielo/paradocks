<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AppointmentCompleted;
use App\Notifications\CouponRewardedNotification;
use App\Services\Coupon\CouponGeneratorService;
use Illuminate\Support\Facades\Log;

/**
 * Generate Reward Coupon Listener
 *
 * Listens to AppointmentCompleted event and generates reward coupons
 * based on auto-generation conditions (service-based or amount-based).
 */
class GenerateRewardCoupon
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected CouponGeneratorService $couponGenerator
    ) {}

    /**
     * Handle the event.
     *
     * Generates reward coupon if conditions are met and sends notification to customer.
     */
    public function handle(AppointmentCompleted $event): void
    {
        $appointment = $event->appointment;

        // Safety check: appointment must have customer
        if (! $appointment->customer) {
            Log::warning('Cannot generate reward coupon: appointment has no customer', [
                'appointment_id' => $appointment->id,
            ]);

            return;
        }

        try {
            // Attempt to generate reward coupon
            $coupon = $this->couponGenerator->generateRewardCoupon($appointment);

            // Send notification to customer
            $appointment->customer->notify(new CouponRewardedNotification($coupon, $appointment));

            Log::info('Reward coupon generated successfully', [
                'appointment_id' => $appointment->id,
                'customer_id' => $appointment->customer_id,
                'coupon_code' => $coupon->code,
                'discount_value' => $coupon->discount_value,
                'discount_type' => $coupon->discount_type,
            ]);
        } catch (\RuntimeException $e) {
            // No matching template found - this is normal, not all appointments generate rewards
            Log::debug('No reward coupon generated (no matching template)', [
                'appointment_id' => $appointment->id,
                'service_id' => $appointment->service_id,
                'subtotal_amount' => $appointment->subtotal_amount,
                'message' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            // Unexpected error - log for investigation
            Log::error('Failed to generate reward coupon', [
                'appointment_id' => $appointment->id,
                'customer_id' => $appointment->customer_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
