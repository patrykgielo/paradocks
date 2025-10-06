<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ServiceAvailability;
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
