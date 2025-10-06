<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceAvailability extends Model
{
    /** @use HasFactory<\Database\Factories\ServiceAvailabilityFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeForDay($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForService($query, int $serviceId)
    {
        return $query->where('service_id', $serviceId);
    }
}
