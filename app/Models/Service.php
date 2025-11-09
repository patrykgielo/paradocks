<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
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
            $parts[] = $days . ' ' . ($days === 1 ? 'dzień' : 'dni');
        }

        if ($hours > 0) {
            $parts[] = $hours . ' ' . ($hours === 1 ? 'godz' : 'godz');
        }

        if ($minutes > 0) {
            $parts[] = $minutes . ' min';
        }

        return !empty($parts) ? implode(', ', $parts) : '0 min';
    }
}
