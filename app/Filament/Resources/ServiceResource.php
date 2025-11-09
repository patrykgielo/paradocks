<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nazwa usługi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->label('Opis')
                    ->columnSpanFull(),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('duration_days')
                            ->label('Dni')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(0)
                            ->default(0)
                            ->suffix('dni')
                            ->helperText('Usługi wielodniowe nie są obsługiwane')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('duration_hours')
                            ->label('Godziny')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(23)
                            ->suffix('godz')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function ($state, $set, $get, $record) {
                                if ($record && $record->duration_minutes) {
                                    $set('duration_hours', floor($record->duration_minutes / 60));
                                }
                            })
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $hours = (int) ($state ?? 0);
                                $minutes = (int) ($get('duration_mins') ?? 0);
                                $set('duration_minutes', ($hours * 60) + $minutes);
                            })
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('duration_mins')
                            ->label('Minuty')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(59)
                            ->step(15)
                            ->suffix('min')
                            ->live(onBlur: true)
                            ->afterStateHydrated(function ($state, $set, $get, $record) {
                                if ($record && $record->duration_minutes) {
                                    $set('duration_mins', $record->duration_minutes % 60);
                                }
                            })
                            ->afterStateUpdated(function ($state, $set, $get) {
                                $hours = (int) ($get('duration_hours') ?? 0);
                                $minutes = (int) ($state ?? 0);
                                $set('duration_minutes', ($hours * 60) + $minutes);
                            })
                            ->dehydrated(false),
                    ]),
                Forms\Components\Hidden::make('duration_minutes')
                    ->default(60)
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Cena')
                    ->required()
                    ->numeric()
                    ->default(0.00)
                    ->prefix('zł'),
                Forms\Components\Toggle::make('is_active')
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa usługi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('formatted_duration')
                    ->label('Czas trwania')
                    ->sortable(query: function ($query, $direction) {
                        return $query->orderBy('duration_minutes', $direction);
                    }),
                Tables\Columns\TextColumn::make('price')
                    ->label('Cena')
                    ->money('PLN')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
