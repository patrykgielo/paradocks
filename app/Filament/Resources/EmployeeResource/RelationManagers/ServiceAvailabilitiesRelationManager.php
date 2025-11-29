<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use App\Models\ServiceAvailability;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceAvailabilitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'serviceAvailabilities';

    protected static ?string $title = 'Dostępności';

    protected static ?string $modelLabel = 'dostępność';

    protected static ?string $pluralModelLabel = 'dostępności';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
                Forms\Components\Select::make('service_id')
                    ->label('Usługa')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->required()
                    ->preload()
                    ->helperText('Wybierz usługę, którą pracownik może wykonywać')
                    ->columnSpan('full'),

                Forms\Components\Select::make('day_of_week')
                    ->label('Dzień tygodnia')
                    ->options([
                        0 => 'Niedziela',
                        1 => 'Poniedziałek',
                        2 => 'Wtorek',
                        3 => 'Środa',
                        4 => 'Czwartek',
                        5 => 'Piątek',
                        6 => 'Sobota',
                    ])
                    ->required()
                    ->native(false)
                    ->helperText('Wybierz dzień tygodnia'),

                Forms\Components\TimePicker::make('start_time')
                    ->label('Godzina rozpoczęcia')
                    ->required()
                    ->seconds(false)
                    ->minutesStep(15)
                    ->default('09:00')
                    ->helperText('Format: HH:MM'),

                Forms\Components\TimePicker::make('end_time')
                    ->label('Godzina zakończenia')
                    ->required()
                    ->seconds(false)
                    ->minutesStep(15)
                    ->default('17:00')
                    ->helperText('Format: HH:MM')
                    ->after('start_time')
                    ->rules([
                        fn ($get, $record, $livewire): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record, $livewire) {
                            $userId = $livewire->getOwnerRecord()->id; // Get employee ID from parent record
                            $serviceId = $get('service_id');
                            $dayOfWeek = $get('day_of_week');
                            $startTime = $get('start_time');

                            if (!$serviceId || $dayOfWeek === null || !$startTime) {
                                return;
                            }

                            // Check for overlapping time slots
                            $query = ServiceAvailability::query()
                                ->where('user_id', $userId)
                                ->where('service_id', $serviceId)
                                ->where('day_of_week', $dayOfWeek)
                                ->where(function ($query) use ($startTime, $value) {
                                    // Check if new slot overlaps with existing slots
                                    $query->where(function ($q) use ($startTime, $value) {
                                        // New start time is within an existing slot
                                        $q->where('start_time', '<=', $startTime)
                                          ->where('end_time', '>', $startTime);
                                    })->orWhere(function ($q) use ($startTime, $value) {
                                        // New end time is within an existing slot
                                        $q->where('start_time', '<', $value)
                                          ->where('end_time', '>=', $value);
                                    })->orWhere(function ($q) use ($startTime, $value) {
                                        // New slot completely contains an existing slot
                                        $q->where('start_time', '>=', $startTime)
                                          ->where('end_time', '<=', $value);
                                    });
                                });

                            // Exclude current record when editing
                            if ($record) {
                                $query->where('id', '!=', $record->id);
                            }

                            if ($query->exists()) {
                                $fail('Ten przedział czasowy nakłada się z istniejącą dostępnością dla tej usługi i dnia.');
                            }
                        },
                    ]),
            ])->columns(3);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('service.name')
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Usługa')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('day_of_week')
                    ->label('Dzień')
                    ->formatStateUsing(fn (int $state): string => match($state) {
                        0 => 'Niedziela',
                        1 => 'Poniedziałek',
                        2 => 'Wtorek',
                        3 => 'Środa',
                        4 => 'Czwartek',
                        5 => 'Piątek',
                        6 => 'Sobota',
                        default => 'Nieznany',
                    })
                    ->badge()
                    ->color(fn (int $state): string => match($state) {
                        0, 6 => 'warning',  // Weekend
                        default => 'success', // Weekday
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Od')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Do')
                    ->time('H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Usługa')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('day_of_week')
                    ->label('Dzień tygodnia')
                    ->options([
                        0 => 'Niedziela',
                        1 => 'Poniedziałek',
                        2 => 'Wtorek',
                        3 => 'Środa',
                        4 => 'Czwartek',
                        5 => 'Piątek',
                        6 => 'Sobota',
                    ]),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->label('Dodaj dostępność'),
            ])
            ->recordActions([
                Actions\EditAction::make()
                    ->label('Edytuj'),
                Actions\DeleteAction::make()
                    ->label('Usuń'),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->label('Usuń zaznaczone'),
                ]),
            ])
            ->emptyStateHeading('Brak dostępności')
            ->emptyStateDescription('Dodaj pierwszą dostępność klikając przycisk powyżej.')
            ->emptyStateIcon('heroicon-o-calendar');
    }
}
