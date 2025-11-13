<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SmsSendResource\Pages;
use App\Models\SmsSend;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SmsSendResource extends Resource
{
    protected static ?string $model = SmsSend::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'SMS';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'SMS History';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Read-only resource, no forms needed
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('template_key')
                    ->label('Template')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone_to')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('message_body')
                    ->label('Message')
                    ->limit(50)
                    ->tooltip(fn (SmsSend $record): string => $record->message_body),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'invalid_number' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('message_length')
                    ->label('Length')
                    ->suffix(' chars'),

                Tables\Columns\TextColumn::make('message_parts')
                    ->label('Parts')
                    ->badge(),

                Tables\Columns\TextColumn::make('sent_at')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'sent' => 'Sent',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'invalid_number' => 'Invalid Number',
                    ]),

                Tables\Filters\SelectFilter::make('template_key')
                    ->label('Template')
                    ->options([
                        'appointment-created' => 'Appointment Created',
                        'appointment-confirmed' => 'Appointment Confirmed',
                        'appointment-reminder-24h' => 'Reminder (24h)',
                        'appointment-reminder-2h' => 'Reminder (2h)',
                        'appointment-follow-up' => 'Follow-up',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false; // Read-only resource
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSmsSends::route('/'),
            'view' => Pages\ViewSmsSend::route('/{record}'),
        ];
    }
}
