<?php

declare(strict_types=1);

namespace App\Filament\Resources\Influencers\Tables;

use App\Models\Influencer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class InfluencersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa / Imię')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email skopiowany!'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('coupons_count')
                    ->label('Liczba kuponów')
                    ->counts('coupons')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_discount_given')
                    ->label('Łączny rabat')
                    ->getStateUsing(fn (Influencer $record) => $record->coupons()->sum('total_discount_given'))
                    ->money('PLN')
                    ->sortable(query: function ($query, $direction) {
                        return $query->withSum('coupons', 'total_discount_given')
                            ->orderBy('coupons_sum_total_discount_given', $direction);
                    })
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('total_bookings')
                    ->label('Rezerwacje')
                    ->getStateUsing(fn (Influencer $record) => $record->coupons()->sum('generated_bookings_count'))
                    ->sortable(query: function ($query, $direction) {
                        return $query->withSum('coupons', 'generated_bookings_count')
                            ->orderBy('coupons_sum_generated_bookings_count', $direction);
                    })
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
