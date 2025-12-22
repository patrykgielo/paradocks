<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Appointment Completed Event
 *
 * Dispatched when an appointment status changes to 'completed'.
 * Triggers reward coupon generation if conditions are met.
 */
class AppointmentCompleted
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Appointment  $appointment  The completed appointment
     */
    public function __construct(
        public Appointment $appointment
    ) {}
}
