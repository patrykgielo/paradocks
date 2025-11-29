<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Appointment Follow-Up Event
 *
 * Dispatched by scheduler 24 hours after a completed appointment.
 * Triggers follow-up email with review request.
 */
class AppointmentFollowUp
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
