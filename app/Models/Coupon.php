<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Coupon Model
 *
 * Represents discount coupons that can be:
 * - Manual: Admin-created for specific campaigns
 * - Auto Service: Generated after completing specific service
 * - Auto Amount: Generated after spending minimum amount
 */
class Coupon extends Model
{
    protected $fillable = [
        'code',
        'type',
        'discount_type',
        'discount_value',
        'condition_service_id',
        'condition_min_amount',
        'uses_count',
        'total_discount_given',
        'generated_bookings_count',
        'influencer_id',
        'is_active',
        'valid_from',
        'valid_until',
        'max_uses',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'condition_min_amount' => 'decimal:2',
        'total_discount_given' => 'decimal:2',
        'uses_count' => 'integer',
        'generated_bookings_count' => 'integer',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'max_uses' => 'integer',
    ];

    // Relationships

    /**
     * Service that triggers auto-generation (for auto_service type)
     */
    public function conditionService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'condition_service_id');
    }

    /**
     * Influencer who owns this coupon (if applicable)
     */
    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }

    /**
     * Usage records for this coupon
     */
    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    /**
     * Appointments that used this coupon
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // Scopes

    /**
     * Only active coupons
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Only valid coupons (within date range and usage limits)
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereRaw('uses_count < max_uses');
            });
    }

    /**
     * Filter by coupon type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Filter by discount type
     */
    public function scopeDiscountType($query, string $discountType)
    {
        return $query->where('discount_type', $discountType);
    }

    /**
     * Expired coupons
     */
    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now());
    }

    /**
     * Coupons for a specific influencer
     */
    public function scopeForInfluencer($query, int $influencerId)
    {
        return $query->where('influencer_id', $influencerId);
    }

    // Accessors & Helpers

    /**
     * Check if coupon is currently valid
     */
    public function isValid(): bool
    {
        // Must be active
        if (! $this->is_active) {
            return false;
        }

        // Check valid_from date
        if ($this->valid_from && $this->valid_from->isFuture()) {
            return false;
        }

        // Check valid_until date
        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        // Check usage limit
        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Check if coupon has usage limit
     */
    public function hasUsageLimit(): bool
    {
        return $this->max_uses !== null;
    }

    /**
     * Get remaining uses
     */
    public function getRemainingUsesAttribute(): ?int
    {
        if (! $this->hasUsageLimit()) {
            return null; // Unlimited
        }

        return max(0, $this->max_uses - $this->uses_count);
    }

    /**
     * Check if coupon is expired
     */
    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }

    /**
     * Calculate discount for given amount
     */
    public function calculateDiscount(float $amount): float
    {
        if ($this->discount_type === 'percentage') {
            return round($amount * ($this->discount_value / 100), 2);
        }

        // Fixed discount
        return min($this->discount_value, $amount); // Don't exceed total amount
    }

    /**
     * Get formatted discount display (e.g., "20%" or "50 PLN")
     */
    public function getFormattedDiscountAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value.'%';
        }

        return number_format($this->discount_value, 2).' PLN';
    }

    /**
     * Get human-readable type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'manual' => 'Ręczny',
            'auto_service' => 'Auto (usługa)',
            'auto_amount' => 'Auto (kwota)',
            default => $this->type,
        };
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(float $discountAmount): void
    {
        $this->increment('uses_count');
        $this->increment('total_discount_given', $discountAmount);
    }

    /**
     * Increment generated bookings counter
     */
    public function incrementGeneratedBookings(): void
    {
        $this->increment('generated_bookings_count');
    }
}
