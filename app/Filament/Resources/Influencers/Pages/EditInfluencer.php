<?php

namespace App\Filament\Resources\Influencers\Pages;

use App\Filament\Resources\Influencers\InfluencerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInfluencer extends EditRecord
{
    protected static string $resource = InfluencerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
