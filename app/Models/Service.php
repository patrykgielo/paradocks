<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        // Existing fields
        'name',
        'description',
        'duration_minutes',
        'price',
        'is_active',
        'sort_order',
        // CMS fields
        'slug',
        'excerpt',
        'body',
        'content',
        'meta_title',
        'meta_description',
        'featured_image',
        'published_at',
        // P0 fields
        'price_from',
        'area_served',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'price_from' => 'decimal:2',
        'duration_minutes' => 'integer',
        'content' => 'array',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function serviceAvailabilities()
    {
        return $this->hasMany(ServiceAvailability::class);
    }

    /**
     * Get the staff members that can perform this service.
     */
    public function staff()
    {
        return $this->belongsToMany(User::class, 'service_staff', 'service_id', 'user_id')
            ->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope: Published services only (published_at not null and in the past)
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: Draft services (published_at is null)
     */
    public function scopeDraft($query)
    {
        return $query->whereNull('published_at');
    }

    /**
     * Boot method for model events
     */
    protected static function booted(): void
    {
        static::creating(function ($service) {
            // Auto-generate slug from name if not provided
            if (empty($service->slug) && ! empty($service->name)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    /**
     * Get the route key name for Laravel route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if the service is published (published_at in the past)
     */
    public function isPublished(): bool
    {
        return $this->published_at && $this->published_at->isPast();
    }

    // Accessors
    public function getFormattedDurationAttribute(): string
    {
        $totalMinutes = $this->duration_minutes;

        $days = floor($totalMinutes / 1440);
        $remainingAfterDays = $totalMinutes % 1440;
        $hours = floor($remainingAfterDays / 60);
        $minutes = $remainingAfterDays % 60;

        $parts = [];

        if ($days > 0) {
            $parts[] = $days.' '.($days === 1 ? 'dzień' : 'dni');
        }

        if ($hours > 0) {
            $parts[] = $hours.' '.($hours === 1 ? 'godz' : 'godz');
        }

        if ($minutes > 0) {
            $parts[] = $minutes.' min';
        }

        return ! empty($parts) ? implode(', ', $parts) : '0 min';
    }
}
