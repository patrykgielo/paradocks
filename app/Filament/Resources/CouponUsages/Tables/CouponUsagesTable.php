<?php

declare(strict_types=1);

namespace App\Filament\Resources\CouponUsages\Tables;

use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponUsagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('coupon.code')
                    ->label('Kod kuponu')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kod skopiowany!')
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Klient')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->url(fn ($record) => $record->customer ? route('filament.admin.resources.customers.edit', ['record' => $record->customer_id]) : null),

                Tables\Columns\TextColumn::make('appointment_id')
                    ->label('ID Wizyty')
                    ->sortable()
                    ->url(fn ($record) => route('filament.admin.resources.appointments.edit', ['record' => $record->appointment_id]))
                    ->color('info'),

                Tables\Columns\TextColumn::make('appointment.service.name')
                    ->label('Usługa')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Wartość rabatu')
                    ->money('PLN')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\TextColumn::make('used_at')
                    ->label('Data użycia')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('coupon.type')
                    ->label('Typ kuponu')
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
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Zarejestrowano')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('coupon_id')
                    ->label('Kupon')
                    ->relationship('coupon', 'code')
                    ->searchable()
                    ->preload()
                    ->native(false),

                SelectFilter::make('customer_id')
                    ->label('Klient')
                    ->relationship('customer', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->searchable()
                    ->preload()
                    ->native(false),

                Filter::make('used_at')
                    ->form([
                        Tables\Filters\Indicators\DatePickerIndicator::make('used_from')
                            ->label('Data użycia od'),
                        Tables\Filters\Indicators\DatePickerIndicator::make('used_until')
                            ->label('Data użycia do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['used_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('used_at', '>=', $date),
                            )
                            ->when(
                                $data['used_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('used_at', '<=', $date),
                            );
                    }),

                SelectFilter::make('type')
                    ->label('Typ kuponu')
                    ->options([
                        'manual' => 'Ręczny',
                        'auto_service' => 'Auto (usługa)',
                        'auto_amount' => 'Auto (kwota)',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! $data['value']) {
                            return $query;
                        }

                        return $query->whereHas('coupon', function (Builder $q) use ($data) {
                            $q->where('type', $data['value']);
                        });
                    })
                    ->native(false),
            ])
            ->recordActions([
                // Read-only - no edit/delete actions
            ])
            ->bulkActions([
                // No bulk actions for read-only resource
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Eksportuj do CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () {
                        // Simple CSV export
                        $usages = \App\Models\CouponUsage::query()
                            ->with(['coupon', 'customer', 'appointment.service'])
                            ->get();

                        $filename = 'coupon-usages-'.now()->format('Y-m-d').'.csv';
                        $headers = [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ];

                        $callback = function () use ($usages) {
                            $file = fopen('php://output', 'w');

                            // Header row
                            fputcsv($file, [
                                'Kod kuponu',
                                'Klient',
                                'Email klienta',
                                'ID Wizyty',
                                'Usługa',
                                'Wartość rabatu (PLN)',
                                'Data użycia',
                                'Typ kuponu',
                            ]);

                            // Data rows
                            foreach ($usages as $usage) {
                                fputcsv($file, [
                                    $usage->coupon->code,
                                    $usage->customer->name,
                                    $usage->customer->email,
                                    $usage->appointment_id,
                                    $usage->appointment->service->name ?? '—',
                                    number_format($usage->discount_amount, 2),
                                    $usage->used_at->format('Y-m-d H:i:s'),
                                    match ($usage->coupon->type) {
                                        'manual' => 'Ręczny',
                                        'auto_service' => 'Auto (usługa)',
                                        'auto_amount' => 'Auto (kwota)',
                                        default => $usage->coupon->type,
                                    },
                                ]);
                            }

                            fclose($file);
                        };

                        return response()->stream($callback, 200, $headers);
                    }),
            ])
            ->defaultSort('used_at', 'desc')
            ->emptyStateHeading('Brak historii użyć')
            ->emptyStateDescription('Kupony nie były jeszcze używane');
    }
}
