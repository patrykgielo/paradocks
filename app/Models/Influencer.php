<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Influencer Model
 *
 * Represents influencers/partners who can have custom discount coupons
 * for their audience.
 */
class Influencer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'notes',
    ];

    // Relationships

    /**
     * Coupons associated with this influencer
     */
    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    // Accessors & Helpers

    /**
     * Get total number of coupons for this influencer
     */
    public function getTotalCouponsAttribute(): int
    {
        return $this->coupons()->count();
    }

    /**
     * Get total discount given through influencer's coupons
     */
    public function getTotalDiscountGivenAttribute(): float
    {
        return $this->coupons()->sum('total_discount_given');
    }

    /**
     * Get total bookings generated through influencer's coupons
     */
    public function getTotalBookingsGeneratedAttribute(): int
    {
        return $this->coupons()->sum('generated_bookings_count');
    }

    /**
     * Get active coupons for this influencer
     */
    public function getActiveCouponsAttribute()
    {
        return $this->coupons()->active()->get();
    }
}
