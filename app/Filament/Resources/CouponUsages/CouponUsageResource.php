<?php

namespace App\Filament\Resources\CouponUsages;

use App\Filament\Resources\CouponUsages\Pages\CreateCouponUsage;
use App\Filament\Resources\CouponUsages\Pages\EditCouponUsage;
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
            'create' => CreateCouponUsage::route('/create'),
            'edit' => EditCouponUsage::route('/{record}/edit'),
        ];
    }
}
