<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Coupon Seeder
 *
 * Creates sample auto-generation template coupons and manual test coupons.
 */
class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Auto-generation template: Service-based (Premium Detailing)
        $premiumService = Service::where('name', 'like', '%Premium%')->orWhere('name', 'like', '%Komplet%')->first();

        if ($premiumService) {
            Coupon::create([
                'code' => 'TEMPLATE-PREMIUM', // Template code (not used for customer rewards)
                'type' => 'auto_service',
                'discount_type' => 'percentage',
                'discount_value' => 15.00, // 15% discount
                'condition_service_id' => $premiumService->id,
                'is_active' => true,
            ]);
        }

        // Auto-generation template: Amount-based (500 PLN threshold)
        Coupon::create([
            'code' => 'TEMPLATE-500PLN',
            'type' => 'auto_amount',
            'discount_type' => 'fixed',
            'discount_value' => 50.00, // 50 PLN off
            'condition_min_amount' => 500.00,
            'is_active' => true,
        ]);

        // Auto-generation template: Amount-based (1000 PLN threshold)
        Coupon::create([
            'code' => 'TEMPLATE-1000PLN',
            'type' => 'auto_amount',
            'discount_type' => 'fixed',
            'discount_value' => 100.00, // 100 PLN off
            'condition_min_amount' => 1000.00,
            'is_active' => true,
        ]);

        // Manual test coupon: Active, unlimited uses
        Coupon::create([
            'code' => 'WELCOME2025',
            'type' => 'manual',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'is_active' => true,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
            'max_uses' => null, // Unlimited
        ]);

        // Manual test coupon: Limited uses
        Coupon::create([
            'code' => 'FIRST50',
            'type' => 'manual',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'is_active' => true,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(1),
            'max_uses' => 50,
        ]);

        // Manual test coupon: Expired (for testing validation)
        Coupon::create([
            'code' => 'EXPIRED2024',
            'type' => 'manual',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'is_active' => true,
            'valid_from' => now()->subMonths(2),
            'valid_until' => now()->subDays(1),
            'max_uses' => null,
        ]);
    }
}
