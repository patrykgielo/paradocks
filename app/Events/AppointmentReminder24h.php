<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Appointment Reminder 24 Hours Event
 *
 * Dispatched by scheduler 24 hours before an appointment.
 * Triggers reminder email to customer.
 */
class AppointmentReminder24h
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Appointment  $appointment  The upcoming appointment
     */
    public function __construct(
        public Appointment $appointment
    ) {}
}
