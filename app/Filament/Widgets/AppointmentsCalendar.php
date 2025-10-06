<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Filament\Widgets\Widget;
use Guava\Calendar\ValueObjects\Event;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Database\Eloquent\Model;

class AppointmentsCalendar extends CalendarWidget
{
    protected static ?int $sort = 1;

    public function getEvents(array $fetchInfo = []): array
    {
        return Appointment::query()
            ->with(['service', 'customer', 'staff'])
            ->when(
                isset($fetchInfo['start']),
                fn ($query) => $query->where('appointment_date', '>=', $fetchInfo['start'])
            )
            ->when(
                isset($fetchInfo['end']),
                fn ($query) => $query->where('appointment_date', '<=', $fetchInfo['end'])
            )
            ->get()
            ->map(function (Appointment $appointment) {
                return Event::make()
                    ->id($appointment->id)
                    ->title(
                        $appointment->customer->name . ' - ' . $appointment->service->name
                    )
                    ->start($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->start_time->format('H:i'))
                    ->end($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->end_time->format('H:i'))
                    ->backgroundColor($this->getEventColor($appointment->status))
                    ->textColor('#ffffff')
                    ->url(AppointmentResource::getUrl('edit', ['record' => $appointment->id]));
            })
            ->toArray();
    }

    protected function getEventColor(string $status): string
    {
        return match ($status) {
            'pending' => '#f59e0b',
            'confirmed' => '#10b981',
            'cancelled' => '#ef4444',
            'completed' => '#6b7280',
            default => '#3b82f6',
        };
    }

    public function onEventClick(array $info = [], ?string $action = null): void
    {
        if (isset($info['event']['id'])) {
            redirect(AppointmentResource::getUrl('edit', ['record' => $info['event']['id']]));
        }
    }
}
