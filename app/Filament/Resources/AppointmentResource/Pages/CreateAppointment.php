<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Services\AppointmentService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $appointmentService = app(AppointmentService::class);

        $validation = $appointmentService->validateAppointment(
            staffId: $data['staff_id'],
            serviceId: $data['service_id'],
            appointmentDate: $data['appointment_date'],
            startTime: $data['start_time'],
            endTime: $data['end_time']
        );

        if (!$validation['valid']) {
            foreach ($validation['errors'] as $error) {
                Notification::make()
                    ->danger()
                    ->title('Błąd walidacji')
                    ->body($error)
                    ->persistent()
                    ->send();
            }

            $this->halt();
        }

        return $data;
    }
}
