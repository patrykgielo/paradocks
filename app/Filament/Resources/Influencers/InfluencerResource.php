<?php

namespace App\Filament\Resources\Influencers;

use App\Filament\Resources\Influencers\Pages\CreateInfluencer;
use App\Filament\Resources\Influencers\Pages\EditInfluencer;
use App\Filament\Resources\Influencers\Pages\ListInfluencers;
use App\Filament\Resources\Influencers\Schemas\InfluencerForm;
use App\Filament\Resources\Influencers\Tables\InfluencersTable;
use App\Models\Influencer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InfluencerResource extends Resource
{
    protected static ?string $model = Influencer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Influencerzy';

    protected static ?string $modelLabel = 'Influencer';

    protected static ?string $pluralModelLabel = 'Influencerzy';

    protected static ?string $navigationGroup = 'Marketing';

    public static function form(Schema $schema): Schema
    {
        return InfluencerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InfluencersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\Influencers\RelationManagers\CouponsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInfluencers::route('/'),
            'create' => CreateInfluencer::route('/create'),
            'edit' => EditInfluencer::route('/{record}/edit'),
        ];
    }
}
