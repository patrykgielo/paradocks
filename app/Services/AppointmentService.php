<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ServiceAvailability;
use App\Models\User;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Check if staff member is available for given time slot
     */
    public function checkStaffAvailability(
        int $staffId,
        int $serviceId,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeAppointmentId = null
    ): bool {
        // Check if staff has availability configured for this day
        $dayOfWeek = $date->dayOfWeek;

        $availability = ServiceAvailability::query()
            ->where('user_id', $staffId)
            ->where('service_id', $serviceId)
            ->where('day_of_week', $dayOfWeek)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // Check if requested time is within availability window
                    $q->whereTime('start_time', '<=', $startTime->format('H:i:s'))
                      ->whereTime('end_time', '>=', $endTime->format('H:i:s'));
                });
            })
            ->exists();

        if (!$availability) {
            return false;
        }

        // Check for conflicting appointments
        $hasConflict = Appointment::query()
            ->where('staff_id', $staffId)
            ->where('appointment_date', $date->format('Y-m-d'))
            ->whereIn('status', ['pending', 'confirmed'])
            ->when($excludeAppointmentId, fn($q) => $q->where('id', '!=', $excludeAppointmentId))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    // New appointment starts during existing appointment
                    $q->whereTime('start_time', '<=', $startTime->format('H:i:s'))
                      ->whereTime('end_time', '>', $startTime->format('H:i:s'));
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New appointment ends during existing appointment
                    $q->whereTime('start_time', '<', $endTime->format('H:i:s'))
                      ->whereTime('end_time', '>=', $endTime->format('H:i:s'));
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New appointment completely contains existing appointment
                    $q->whereTime('start_time', '>=', $startTime->format('H:i:s'))
                      ->whereTime('end_time', '<=', $endTime->format('H:i:s'));
                });
            })
            ->exists();

        return !$hasConflict;
    }

    /**
     * Get available time slots for a service on a specific date
     */
    public function getAvailableTimeSlots(
        int $serviceId,
        int $staffId,
        Carbon $date,
        int $serviceDurationMinutes
    ): array {
        $dayOfWeek = $date->dayOfWeek;

        // Get staff availability for this day and service
        $availabilities = ServiceAvailability::query()
            ->where('user_id', $staffId)
            ->where('service_id', $serviceId)
            ->where('day_of_week', $dayOfWeek)
            ->get();

        if ($availabilities->isEmpty()) {
            return [];
        }

        $timeSlots = [];

        foreach ($availabilities as $availability) {
            $currentSlot = Carbon::parse($availability->start_time);
            $endOfAvailability = Carbon::parse($availability->end_time);

            while ($currentSlot->copy()->addMinutes($serviceDurationMinutes)->lte($endOfAvailability)) {
                $slotEnd = $currentSlot->copy()->addMinutes($serviceDurationMinutes);

                // Check if this slot is available
                if ($this->checkStaffAvailability(
                    $staffId,
                    $serviceId,
                    $date,
                    $currentSlot,
                    $slotEnd
                )) {
                    $timeSlots[] = [
                        'start' => $currentSlot->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'datetime_start' => $date->format('Y-m-d') . ' ' . $currentSlot->format('H:i'),
                        'datetime_end' => $date->format('Y-m-d') . ' ' . $slotEnd->format('H:i'),
                    ];
                }

                // Move to next slot (15 minute intervals)
                $currentSlot->addMinutes(15);
            }
        }

        return $timeSlots;
    }

    /**
     * Check if ANY staff member is available for the given time slot
     */
    public function isAnyStaffAvailable(
        int $serviceId,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeAppointmentId = null
    ): bool {
        // Get all staff members who can perform this service
        $staffMembers = User::whereHas('roles', function ($query) {
            $query->where('name', 'staff');
        })->whereHas('serviceAvailabilities', function ($query) use ($serviceId) {
            $query->where('service_id', $serviceId);
        })->get();

        // Check if at least one staff member is available
        foreach ($staffMembers as $staff) {
            if ($this->checkStaffAvailability(
                $staff->id,
                $serviceId,
                $date,
                $startTime,
                $endTime,
                $excludeAppointmentId
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the first available staff member for the given time slot
     *
     * @return int|null Staff ID if available, null if no staff available
     */
    public function findFirstAvailableStaff(
        int $serviceId,
        Carbon $date,
        Carbon $startTime,
        Carbon $endTime,
        ?int $excludeAppointmentId = null
    ): ?int {
        // Get all staff members who can perform this service
        $staffMembers = User::whereHas('roles', function ($query) {
            $query->where('name', 'staff');
        })->whereHas('serviceAvailabilities', function ($query) use ($serviceId) {
            $query->where('service_id', $serviceId);
        })->get();

        // Find first available staff member
        foreach ($staffMembers as $staff) {
            if ($this->checkStaffAvailability(
                $staff->id,
                $serviceId,
                $date,
                $startTime,
                $endTime,
                $excludeAppointmentId
            )) {
                return $staff->id;
            }
        }

        return null;
    }

    /**
     * Get available time slots across ALL staff members for a service on a specific date
     */
    public function getAvailableSlotsAcrossAllStaff(
        int $serviceId,
        Carbon $date,
        int $serviceDurationMinutes
    ): array {
        // Get all staff members who can perform this service
        $staffMembers = User::whereHas('roles', function ($query) {
            $query->where('name', 'staff');
        })->whereHas('serviceAvailabilities', function ($query) use ($serviceId) {
            $query->where('service_id', $serviceId);
        })->get();

        if ($staffMembers->isEmpty()) {
            return [];
        }

        $allSlots = [];
        $slotInterval = config('booking.slot_interval_minutes', 15);
        $businessHours = config('booking.business_hours');
        $businessStart = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHours['start']);
        $businessEnd = Carbon::parse($date->format('Y-m-d') . ' ' . $businessHours['end']);

        // Generate all possible slots within business hours
        $currentSlot = $businessStart->copy();

        while ($currentSlot->copy()->addMinutes($serviceDurationMinutes)->lte($businessEnd)) {
            $slotEnd = $currentSlot->copy()->addMinutes($serviceDurationMinutes);

            // Check if this slot is within business hours completely
            if (!$this->isWithinBusinessHours($currentSlot, $slotEnd)) {
                $currentSlot->addMinutes($slotInterval);
                continue;
            }

            // Check if ANY staff member is available for this slot
            if ($this->isAnyStaffAvailable($serviceId, $date, $currentSlot, $slotEnd)) {
                $slotKey = $currentSlot->format('H:i');

                // Avoid duplicate slots
                if (!isset($allSlots[$slotKey])) {
                    $allSlots[$slotKey] = [
                        'start' => $currentSlot->format('H:i'),
                        'end' => $slotEnd->format('H:i'),
                        'datetime_start' => $date->format('Y-m-d') . ' ' . $currentSlot->format('H:i'),
                        'datetime_end' => $date->format('Y-m-d') . ' ' . $slotEnd->format('H:i'),
                    ];
                }
            }

            $currentSlot->addMinutes($slotInterval);
        }

        return array_values($allSlots);
    }

    /**
     * Check if the given time range is within business hours
     */
    public function isWithinBusinessHours(Carbon $startTime, Carbon $endTime): bool
    {
        $businessHours = config('booking.business_hours');
        $businessStart = Carbon::parse($startTime->format('Y-m-d') . ' ' . $businessHours['start']);
        $businessEnd = Carbon::parse($startTime->format('Y-m-d') . ' ' . $businessHours['end']);

        return $startTime->gte($businessStart) && $endTime->lte($businessEnd);
    }

    /**
     * Check if the appointment meets the advance booking requirement (24h minimum)
     */
    public function meetsAdvanceBookingRequirement(Carbon $appointmentDateTime): bool
    {
        $advanceHours = config('booking.advance_booking_hours', 24);
        $minimumDateTime = now()->addHours($advanceHours);

        return $appointmentDateTime->gte($minimumDateTime);
    }

    /**
     * Validate appointment booking
     */
    public function validateAppointment(
        int $staffId,
        int $serviceId,
        string $appointmentDate,
        string $startTime,
        string $endTime,
        ?int $excludeAppointmentId = null
    ): array {
        $errors = [];

        $date = Carbon::parse($appointmentDate);
        $start = Carbon::parse($appointmentDate . ' ' . $startTime);
        $end = Carbon::parse($appointmentDate . ' ' . $endTime);

        // Check if date is in the past
        if ($date->isPast() && !$date->isToday()) {
            $errors[] = 'Nie można zarezerwować wizyty w przeszłości.';
        }

        // Check 24-hour advance booking requirement
        if (!$this->meetsAdvanceBookingRequirement($start)) {
            $errors[] = 'Rezerwacja musi być dokonana co najmniej 24 godziny przed terminem wizyty.';
        }

        // Check if within business hours
        if (!$this->isWithinBusinessHours($start, $end)) {
            $businessHours = config('booking.business_hours');
            $errors[] = sprintf(
                'Wizyta musi się odbywać w godzinach pracy: %s - %s.',
                $businessHours['start'],
                $businessHours['end']
            );
        }

        // Check if start time is before end time
        if ($start->gte($end)) {
            $errors[] = 'Czas rozpoczęcia musi być przed czasem zakończenia.';
        }

        // Check staff availability
        if (!$this->checkStaffAvailability($staffId, $serviceId, $date, $start, $end, $excludeAppointmentId)) {
            $errors[] = 'Wybrany termin nie jest dostępny. Personel jest zajęty lub nie pracuje w tym czasie.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
