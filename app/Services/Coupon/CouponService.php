<?php

declare(strict_types=1);

namespace App\Services\Coupon;

use App\Models\Appointment;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;

/**
 * Coupon Service
 *
 * Handles coupon validation, application to appointments, and usage tracking.
 */
class CouponService
{
    /**
     * Validate coupon code for a specific user
     *
     * @param  string  $code  The coupon code
     * @param  User  $user  The user trying to use the coupon
     * @return array{valid: bool, coupon: Coupon|null, message: string|null}
     */
    public function validateCoupon(string $code, User $user): array
    {
        // Find coupon by code
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (! $coupon) {
            return [
                'valid' => false,
                'coupon' => null,
                'message' => 'Kod rabatowy nie istnieje.',
            ];
        }

        // Check if coupon is active
        if (! $coupon->is_active) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'message' => 'Ten kod rabatowy jest nieaktywny.',
            ];
        }

        // Check valid_from date
        if ($coupon->valid_from && $coupon->valid_from->isFuture()) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'message' => 'Ten kod rabatowy nie jest jeszcze ważny.',
            ];
        }

        // Check valid_until date
        if ($coupon->valid_until && $coupon->valid_until->isPast()) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'message' => 'Ten kod rabatowy wygasł.',
            ];
        }

        // Check usage limit
        if ($coupon->max_uses && $coupon->uses_count >= $coupon->max_uses) {
            return [
                'valid' => false,
                'coupon' => $coupon,
                'message' => 'Ten kod rabatowy został już wykorzystany maksymalną ilość razy.',
            ];
        }

        // All checks passed
        return [
            'valid' => true,
            'coupon' => $coupon,
            'message' => null,
        ];
    }

    /**
     * Apply coupon to appointment
     *
     * Calculates discount and updates appointment pricing fields.
     *
     * @param  Appointment  $appointment  The appointment to apply coupon to
     * @param  string  $code  The coupon code
     * @return array{success: bool, appointment: Appointment|null, discount: float|null, message: string|null}
     */
    public function applyCoupon(Appointment $appointment, string $code): array
    {
        // Validate coupon
        $validation = $this->validateCoupon($code, $appointment->customer);

        if (! $validation['valid']) {
            return [
                'success' => false,
                'appointment' => null,
                'discount' => null,
                'message' => $validation['message'],
            ];
        }

        $coupon = $validation['coupon'];

        // Calculate discount
        $discountAmount = $coupon->calculateDiscount($appointment->subtotal_amount);

        // Update appointment pricing
        $appointment->update([
            'coupon_id' => $coupon->id,
            'discount_amount' => $discountAmount,
            'total_amount' => $appointment->subtotal_amount - $discountAmount,
        ]);

        return [
            'success' => true,
            'appointment' => $appointment,
            'discount' => $discountAmount,
            'message' => 'Kod rabatowy został zastosowany pomyślnie.',
        ];
    }

    /**
     * Record coupon usage
     *
     * Creates usage record and updates coupon statistics.
     * Should be called when appointment is confirmed/completed.
     *
     * @param  Coupon  $coupon  The coupon that was used
     * @param  Appointment  $appointment  The appointment where coupon was used
     * @return CouponUsage The usage record
     */
    public function recordUsage(Coupon $coupon, Appointment $appointment): CouponUsage
    {
        // Create usage record
        $usage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id,
            'discount_amount' => $appointment->discount_amount,
            'used_at' => now(),
        ]);

        // Update coupon statistics
        $coupon->incrementUsage($appointment->discount_amount);

        // If appointment is confirmed/completed, increment generated bookings
        if (in_array($appointment->status, ['confirmed', 'completed'])) {
            $coupon->incrementGeneratedBookings();
        }

        return $usage;
    }

    /**
     * Remove coupon from appointment
     */
    public function removeCoupon(Appointment $appointment): Appointment
    {
        $appointment->update([
            'coupon_id' => null,
            'discount_amount' => 0,
            'total_amount' => $appointment->subtotal_amount,
        ]);

        return $appointment;
    }

    /**
     * Calculate pricing for appointment
     *
     * Helper method to calculate subtotal, discount, and total.
     *
     * @param  float  $servicePrice  Base service price
     * @param  string|null  $couponCode  Optional coupon code
     * @return array{subtotal: float, discount: float, total: float, coupon: Coupon|null}
     */
    public function calculatePricing(Appointment $appointment, float $servicePrice, ?string $couponCode = null): array
    {
        $subtotal = $servicePrice;
        $discount = 0;
        $coupon = null;

        if ($couponCode) {
            $validation = $this->validateCoupon($couponCode, $appointment->customer);

            if ($validation['valid']) {
                $coupon = $validation['coupon'];
                $discount = $coupon->calculateDiscount($subtotal);
            }
        }

        $total = $subtotal - $discount;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $coupon,
        ];
    }
}
