<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Appointment Reminder 2 Hours Event
 *
 * Dispatched by scheduler 2 hours before an appointment.
 * Triggers final reminder email to customer.
 */
class AppointmentReminder2h
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Appointment $appointment The upcoming appointment
     */
    public function __construct(
        public Appointment $appointment
    ) {
    }
}
