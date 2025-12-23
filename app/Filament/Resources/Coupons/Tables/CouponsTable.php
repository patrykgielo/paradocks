<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\Coupon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kod')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kod skopiowany!')
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->colors([
                        'gray' => 'manual',
                        'info' => 'auto_service',
                        'success' => 'auto_amount',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'manual' => 'Ręczny',
                        'auto_service' => 'Auto (usługa)',
                        'auto_amount' => 'Auto (kwota)',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('condition')
                    ->label('Warunek')
                    ->getStateUsing(function (Coupon $record): ?string {
                        if ($record->type === 'auto_service' && $record->conditionService) {
                            return $record->conditionService->name;
                        }
                        if ($record->type === 'auto_amount' && $record->condition_min_amount) {
                            return '≥ '.number_format($record->condition_min_amount, 2).' PLN';
                        }

                        return '—';
                    })
                    ->searchable(query: function ($query, $search) {
                        return $query->whereHas('conditionService', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('discount')
                    ->label('Rabat')
                    ->getStateUsing(function (Coupon $record): string {
                        return $record->formatted_discount;
                    })
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('discount_value', $direction);
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function (Coupon $record): string {
                        if (! $record->is_active) {
                            return 'nieaktywny';
                        }
                        if ($record->isExpired()) {
                            return 'wygasły';
                        }
                        if ($record->max_uses && $record->uses_count >= $record->max_uses) {
                            return 'wyczerpany';
                        }

                        return 'aktywny';
                    })
                    ->colors([
                        'success' => 'aktywny',
                        'gray' => 'nieaktywny',
                        'warning' => 'wygasły',
                        'danger' => 'wyczerpany',
                    ]),

                Tables\Columns\TextColumn::make('uses_count')
                    ->label('Użycia')
                    ->getStateUsing(function (Coupon $record): string {
                        $used = $record->uses_count;
                        $max = $record->max_uses ?? '∞';

                        return "{$used} / {$max}";
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Wygasa')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('Bezterminowo')
                    ->color(fn (Coupon $record) => $record->isExpired() ? 'danger' : null),

                Tables\Columns\TextColumn::make('influencer.name')
                    ->label('Influencer')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_discount_given')
                    ->label('Łączny rabat')
                    ->money('PLN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ')
                    ->options([
                        'manual' => 'Ręczny',
                        'auto_service' => 'Auto (usługa)',
                        'auto_amount' => 'Auto (kwota)',
                    ])
                    ->native(false),

                TernaryFilter::make('is_active')
                    ->label('Status aktywności')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Tylko aktywne')
                    ->falseLabel('Tylko nieaktywne')
                    ->native(false),

                SelectFilter::make('status')
                    ->label('Status ważności')
                    ->options([
                        'active' => 'Aktywne',
                        'expired' => 'Wygasłe',
                        'exhausted' => 'Wyczerpane',
                    ])
                    ->query(function ($query, $state) {
                        if (! $state['value']) {
                            return $query;
                        }

                        return match ($state['value']) {
                            'active' => $query->where('is_active', true)
                                ->where(function ($q) {
                                    $q->whereNull('valid_until')
                                        ->orWhere('valid_until', '>=', now());
                                })
                                ->where(function ($q) {
                                    $q->whereNull('max_uses')
                                        ->orWhereRaw('uses_count < max_uses');
                                }),
                            'expired' => $query->where('valid_until', '<', now()),
                            'exhausted' => $query->whereNotNull('max_uses')
                                ->whereRaw('uses_count >= max_uses'),
                            default => $query,
                        };
                    })
                    ->native(false),

                SelectFilter::make('influencer_id')
                    ->label('Influencer')
                    ->relationship('influencer', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->recordActions([
                ViewAction::make()
                    ->infolist(fn (Infolist $infolist) => self::viewInfolist($infolist)),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')
                        ->label('Aktywuj zaznaczone')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => true]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('deactivate')
                        ->label('Dezaktywuj zaznaczone')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->update(['is_active' => false]);
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Infolist for view action - display coupon details and usage stats
     */
    protected static function viewInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informacje o kuponie')
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kod')
                            ->copyable(),

                        TextEntry::make('type')
                            ->label('Typ')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'manual' => 'Ręczny',
                                'auto_service' => 'Auto (usługa)',
                                'auto_amount' => 'Auto (kwota)',
                                default => $state,
                            }),

                        TextEntry::make('formatted_discount')
                            ->label('Rabat'),

                        TextEntry::make('is_active')
                            ->label('Status')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktywny' : 'Nieaktywny')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ])
                    ->columns(2),

                Section::make('Statystyki użycia')
                    ->schema([
                        TextEntry::make('uses_count')
                            ->label('Liczba użyć'),

                        TextEntry::make('max_uses')
                            ->label('Maksymalne użycia')
                            ->placeholder('Bez limitu'),

                        TextEntry::make('total_discount_given')
                            ->label('Łączna wartość rabatów')
                            ->money('PLN'),

                        TextEntry::make('generated_bookings_count')
                            ->label('Wygenerowane rezerwacje'),
                    ])
                    ->columns(2),

                Section::make('Ważność')
                    ->schema([
                        TextEntry::make('valid_from')
                            ->label('Ważny od')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Od razu'),

                        TextEntry::make('valid_until')
                            ->label('Ważny do')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('Bezterminowo'),
                    ])
                    ->columns(2),
            ]);
    }
}
