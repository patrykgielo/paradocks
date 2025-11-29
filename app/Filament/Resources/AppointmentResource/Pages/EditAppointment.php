<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use App\Models\User;
use App\Services\AppointmentService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Validate staff role
        if (isset($data['staff_id'])) {
            $staff = User::find($data['staff_id']);
            if ($staff && ! $staff->hasRole('staff')) {
                Notification::make()
                    ->danger()
                    ->title('Błąd walidacji')
                    ->body('Tylko użytkownicy z rolą "staff" mogą być przypisani do wizyt.')
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }

        // Only validate if appointment details changed
        $original = $this->record->getOriginal();

        $changed = $data['staff_id'] != $original['staff_id']
            || $data['appointment_date'] != $original['appointment_date']
            || $data['start_time'] != $original['start_time']
            || $data['end_time'] != $original['end_time'];

        if ($changed) {
            $appointmentService = app(AppointmentService::class);

            $validation = $appointmentService->validateAppointment(
                staffId: $data['staff_id'],
                serviceId: $data['service_id'],
                appointmentDate: $data['appointment_date'],
                startTime: $data['start_time'],
                endTime: $data['end_time'],
                excludeAppointmentId: $this->record->id
            );

            if (! $validation['valid']) {
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
        }

        return $data;
    }
}
