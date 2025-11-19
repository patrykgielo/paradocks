<?php

namespace App\Filament\Resources\StaffVacationPeriodResource\Pages;

use App\Filament\Resources\StaffVacationPeriodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStaffVacationPeriod extends EditRecord
{
    protected static string $resource = StaffVacationPeriodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
