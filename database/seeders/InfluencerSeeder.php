<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Influencer;
use Illuminate\Database\Seeder;

/**
 * Influencer Seeder
 *
 * Creates sample influencers with associated discount coupons.
 */
class InfluencerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Influencer 1: Auto detailing blogger
        $influencer1 = Influencer::create([
            'name' => 'Jan Kowalski - AutoBlog.pl',
            'email' => 'jan.kowalski@autoblog.pl',
            'phone' => '+48 500 100 200',
            'notes' => 'Partner agreement: 15% discount for followers, valid until 2025-06-30',
        ]);

        // Create coupon for influencer 1
        Coupon::create([
            'code' => 'AUTOBLOG15',
            'type' => 'manual',
            'discount_type' => 'percentage',
            'discount_value' => 15.00,
            'influencer_id' => $influencer1->id,
            'is_active' => true,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(6),
            'max_uses' => 100,
        ]);

        // Influencer 2: Instagram car influencer
        $influencer2 = Influencer::create([
            'name' => 'Anna Nowak - @CarsDaily_PL',
            'email' => 'anna@carsdaily.com',
            'phone' => '+48 600 200 300',
            'notes' => 'Instagram partnership: 50 PLN fixed discount, 50 uses',
        ]);

        // Create coupon for influencer 2
        Coupon::create([
            'code' => 'CARSDAILY50',
            'type' => 'manual',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'influencer_id' => $influencer2->id,
            'is_active' => true,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
            'max_uses' => 50,
        ]);

        // Influencer 3: YouTube car channel
        $influencer3 = Influencer::create([
            'name' => 'Piotr Wiśniewski - Moto Kanał YT',
            'email' => 'piotr@motokanal.pl',
            'phone' => '+48 700 300 400',
            'notes' => 'YouTube sponsorship: 20% discount, unlimited uses until end of year',
        ]);

        // Create coupon for influencer 3
        Coupon::create([
            'code' => 'MOTOKANAL20',
            'type' => 'manual',
            'discount_type' => 'percentage',
            'discount_value' => 20.00,
            'influencer_id' => $influencer3->id,
            'is_active' => true,
            'valid_from' => now(),
            'valid_until' => now()->endOfYear(),
            'max_uses' => null, // Unlimited
        ]);
    }
}
