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
        'location_address',
        'location_latitude',
        'location_longitude',
        'location_place_id',
        'location_components',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'location_components' => 'array',
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
        // Only pending or confirmed appointments can be cancelled
        if (! in_array($this->status, ['pending', 'confirmed'])) {
            return false;
        }

        // Check if appointment is in the future
        if ($this->appointment_date < now()->toDateString()) {
            return false;
        }

        // Check 24-hour cancellation policy
        $appointmentDateTime = \Carbon\Carbon::parse(
            $this->appointment_date->format('Y-m-d').' '.$this->start_time->format('H:i:s')
        );

        $cancellationHours = config('booking.cancellation_hours', 24);
        $cancellationDeadline = $appointmentDateTime->subHours($cancellationHours);

        return now()->lte($cancellationDeadline);
    }

    public function getCancellationDeadlineAttribute(): string
    {
        if (! $this->appointment_date || ! $this->start_time) {
            return '';
        }

        $appointmentDateTime = \Carbon\Carbon::parse(
            $this->appointment_date->format('Y-m-d').' '.$this->start_time->format('H:i:s')
        );

        $cancellationHours = config('booking.cancellation_hours', 24);
        $deadline = $appointmentDateTime->subHours($cancellationHours);

        return $deadline->format('Y-m-d H:i');
    }

    public function getFormattedLocationAttribute(): ?string
    {
        if ($this->location_address) {
            return $this->location_address;
        }

        // Fallback to customer's legacy address fields
        if ($this->customer) {
            $parts = array_filter([
                $this->customer->street_name,
                $this->customer->street_number,
                $this->customer->postal_code,
                $this->customer->city,
            ]);

            return ! empty($parts) ? implode(', ', $parts) : null;
        }

        return null;
    }

    /**
     * Get Google Maps link for viewing location
     * Opens map with location marker
     */
    public function getGoogleMapsLinkAttribute(): ?string
    {
        // Priority 1: Use Place ID with /place/ format (most reliable marker display)
        if (! empty($this->location_place_id)) {
            return 'https://www.google.com/maps/place/?q=place_id:'.urlencode($this->location_place_id);
        }

        // Priority 2: Use coordinates with /place/ format (reliable marker)
        if (! empty($this->location_latitude) && ! empty($this->location_longitude)) {
            $lat = round($this->location_latitude, 8);
            $lng = round($this->location_longitude, 8);

            return "https://www.google.com/maps/place/{$lat},{$lng}";
        }

        // Priority 3: Use address string with simple query format
        if (! empty($this->location_address)) {
            return 'https://www.google.com/maps?q='.urlencode($this->location_address);
        }

        // No location data available
        return null;
    }

    /**
     * Get Google Maps directions link
     * Opens directly in navigation/directions mode
     */
    public function getGoogleMapsDirectionsLinkAttribute(): ?string
    {
        $baseUrl = 'https://www.google.com/maps/dir/?api=1';

        // Use coordinates with Place ID for best accuracy
        if (! empty($this->location_latitude) && ! empty($this->location_longitude)) {
            $destination = round($this->location_latitude, 8).','.round($this->location_longitude, 8);
            $url = $baseUrl.'&destination='.urlencode($destination);

            // Add Place ID if available (increases accuracy)
            if (! empty($this->location_place_id)) {
                $url .= '&destination_place_id='.urlencode($this->location_place_id);
            }

            return $url;
        }

        // Fallback to address
        if (! empty($this->location_address)) {
            return $baseUrl.'&destination='.urlencode($this->location_address);
        }

        // No location data available
        return null;
    }

    /**
     * Check if appointment has location data
     */
    public function hasLocationData(): bool
    {
        return ! empty($this->location_place_id)
            || (! empty($this->location_latitude) && ! empty($this->location_longitude))
            || ! empty($this->location_address);
    }
}
