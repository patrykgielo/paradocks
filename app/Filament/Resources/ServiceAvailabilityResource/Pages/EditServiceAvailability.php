<?php

namespace App\Filament\Resources\ServiceAvailabilityResource\Pages;

use App\Filament\Resources\ServiceAvailabilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceAvailability extends EditRecord
{
    protected static string $resource = ServiceAvailabilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
