<?php

declare(strict_types=1);

namespace App\Services\Coupon;

use App\Models\Appointment;
use App\Models\Coupon;
use App\Models\Influencer;
use Illuminate\Support\Str;

/**
 * Coupon Generator Service
 *
 * Handles generation of unique coupon codes for both automatic rewards
 * and manual influencer coupons.
 */
class CouponGeneratorService
{
    /**
     * Generate a reward coupon after appointment completion
     *
     * @param  Appointment  $appointment  The completed appointment that triggers reward
     * @return Coupon The generated coupon
     */
    public function generateRewardCoupon(Appointment $appointment): Coupon
    {
        // Find matching auto-generation coupon configuration
        $templateCoupon = $this->findMatchingTemplate($appointment);

        if (! $templateCoupon) {
            throw new \RuntimeException('No matching coupon template found for this appointment');
        }

        // Generate unique code
        $code = $this->generateUniqueCode();

        // Calculate expiry date (30 days from now by default)
        $validUntil = now()->addDays(30);

        // Create new coupon for customer
        $coupon = Coupon::create([
            'code' => $code,
            'type' => 'manual', // Reward coupons are treated as manual after generation
            'discount_type' => $templateCoupon->discount_type,
            'discount_value' => $templateCoupon->discount_value,
            'condition_service_id' => null,
            'condition_min_amount' => null,
            'influencer_id' => null,
            'is_active' => true,
            'valid_from' => now(),
            'valid_until' => $validUntil,
            'max_uses' => 1, // One-time use for reward coupons
        ]);

        return $coupon;
    }

    /**
     * Generate an influencer coupon
     *
     * @param  Influencer  $influencer  The influencer
     * @param  array  $params  Coupon parameters (discount_type, discount_value, max_uses, valid_until, etc.)
     * @return Coupon The generated coupon
     */
    public function generateInfluencerCoupon(Influencer $influencer, array $params): Coupon
    {
        $code = $this->generateUniqueCode();

        return Coupon::create([
            'code' => $code,
            'type' => 'manual',
            'discount_type' => $params['discount_type'],
            'discount_value' => $params['discount_value'],
            'condition_service_id' => null,
            'condition_min_amount' => null,
            'influencer_id' => $influencer->id,
            'is_active' => $params['is_active'] ?? true,
            'valid_from' => $params['valid_from'] ?? now(),
            'valid_until' => $params['valid_until'] ?? null,
            'max_uses' => $params['max_uses'] ?? null, // Null = unlimited
        ]);
    }

    /**
     * Generate a unique coupon code
     *
     * @param  string  $prefix  Code prefix (default: PD-)
     * @return string Unique code (e.g., PD-A3F9K2)
     */
    public function generateUniqueCode(string $prefix = 'PD-'): string
    {
        do {
            // Generate 6 random alphanumeric characters (uppercase)
            $randomPart = strtoupper(Str::random(6));
            $code = $prefix.$randomPart;

            // Check if code already exists
            $exists = Coupon::where('code', $code)->exists();
        } while ($exists);

        return $code;
    }

    /**
     * Find matching template coupon for appointment
     *
     * Checks auto_service and auto_amount type coupons to find match.
     */
    protected function findMatchingTemplate(Appointment $appointment): ?Coupon
    {
        // Priority 1: Service-based match (auto_service type)
        $serviceBased = Coupon::active()
            ->ofType('auto_service')
            ->where('condition_service_id', $appointment->service_id)
            ->first();

        if ($serviceBased) {
            return $serviceBased;
        }

        // Priority 2: Amount-based match (auto_amount type)
        if ($appointment->subtotal_amount > 0) {
            $amountBased = Coupon::active()
                ->ofType('auto_amount')
                ->where('condition_min_amount', '<=', $appointment->subtotal_amount)
                ->orderBy('condition_min_amount', 'desc') // Get highest threshold that matches
                ->first();

            if ($amountBased) {
                return $amountBased;
            }
        }

        return null;
    }
}
