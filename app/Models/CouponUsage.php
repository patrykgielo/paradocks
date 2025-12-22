<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Coupon Usage Model
 *
 * Tracks every instance of a coupon being used for reporting and analytics.
 */
class CouponUsage extends Model
{
    protected $fillable = [
        'coupon_id',
        'appointment_id',
        'customer_id',
        'discount_amount',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
        'used_at' => 'datetime',
    ];

    // Relationships

    /**
     * The coupon that was used
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * The appointment where coupon was used
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * The customer who used the coupon
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Scopes

    /**
     * Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('used_at', [$startDate, $endDate]);
    }

    /**
     * Filter by customer
     */
    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Filter by coupon
     */
    public function scopeForCoupon($query, int $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }
}
