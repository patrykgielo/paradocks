<?php

namespace App\Filament\Resources\CouponUsages;

use App\Filament\Resources\CouponUsages\Pages\ListCouponUsages;
use App\Filament\Resources\CouponUsages\Schemas\CouponUsageForm;
use App\Filament\Resources\CouponUsages\Tables\CouponUsagesTable;
use App\Models\CouponUsage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CouponUsageResource extends Resource
{
    protected static ?string $model = CouponUsage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Historia użyć';

    protected static ?string $modelLabel = 'Użycie kuponu';

    protected static ?string $pluralModelLabel = 'Historia użyć kuponów';

    protected static ?string $navigationGroup = 'Marketing';

    // Read-only resource - no create/edit
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return CouponUsageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CouponUsagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCouponUsages::route('/'),
            // No create/edit pages - read-only
        ];
    }
}
