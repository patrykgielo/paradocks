<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    /** @use HasFactory<\Database\Factories\AppointmentFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'customer_id',
        'staff_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status',
        'notes',
        'cancellation_reason',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Relationships
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    // Scopes
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where('appointment_date', '>=', now()->toDateString())
            ->orderBy('appointment_date')
            ->orderBy('start_time');
    }

    public function scopeForCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('appointment_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getIsUpcomingAttribute(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'])
            && $this->appointment_date >= now()->toDateString();
    }

    public function getIsPastAttribute(): bool
    {
        return $this->appointment_date < now()->toDateString();
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'])
            && $this->appointment_date >= now()->toDateString();
    }
}
