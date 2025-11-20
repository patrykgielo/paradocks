<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\LocalizedDatePicker;
use App\Filament\Resources\StaffVacationPeriodResource\Pages;
use App\Filament\Resources\StaffVacationPeriodResource\RelationManagers;
use App\Filament\Tables\Columns\LocalizedDateColumn;
use App\Filament\Tables\Columns\LocalizedDateTimeColumn;
use App\Models\StaffVacationPeriod;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffVacationPeriodResource extends Resource
{
    protected static ?string $model = StaffVacationPeriod::class;

    protected static ?string $navigationIcon = 'heroicon-o-sun';

    protected static ?string $navigationGroup = 'Harmonogramy';

    protected static ?string $navigationLabel = 'Wnioski o Czas Wolny';

    protected static ?string $modelLabel = 'Urlop';

    protected static ?string $pluralModelLabel = 'Urlopy / Wnioski o Czas Wolny';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pracownik')
                            ->relationship('user', 'first_name', function (Builder $query) {
                                $query->role('staff');
                            })
                            ->getOptionLabelFromRecordUsing(fn (User $record) => $record->name)
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Okres urlopu')
                    ->schema([
                        LocalizedDatePicker::make('start_date')
                            ->label('Data rozpoczęcia')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state && $get('end_date') && $state > $get('end_date')) {
                                    $set('end_date', $state);
                                }
                            }),

                        LocalizedDatePicker::make('end_date')
                            ->label('Data zakończenia')
                            ->required()
                            ->after('start_date')
                            ->reactive()
                            ->helperText(function (callable $get) {
                                $start = $get('start_date');
                                $end = $get('end_date');

                                if ($start && $end) {
                                    $startDate = \Carbon\Carbon::parse($start);
                                    $endDate = \Carbon\Carbon::parse($end);
                                    $days = $startDate->diffInDays($endDate) + 1;
                                    return "Długość urlopu: {$days} " . ($days === 1 ? 'dzień' : 'dni');
                                }

                                return '';
                            }),
                    ])->columns(2),

                Forms\Components\Section::make('Szczegóły')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('Powód/Typ urlopu')
                            ->rows(3)
                            ->placeholder('np. "Urlop wypoczynkowy", "Urlop na żądanie", "Zwolnienie lekarskie"')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_approved')
                            ->label('Zatwierdzony')
                            ->default(false)
                            ->helperText('Czy ten urlop został zatwierdzony przez managera?')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pracownik')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                LocalizedDateColumn::make('start_date')
                    ->label('Od')
                    ->sortable(),

                LocalizedDateColumn::make('end_date')
                    ->label('Do')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration')
                    ->label('Długość')
                    ->formatStateUsing(function (StaffVacationPeriod $record) {
                        $days = $record->getDurationInDays();
                        return $days . ' ' . ($days === 1 ? 'dzień' : 'dni');
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Powód')
                    ->limit(30)
                    ->placeholder('Brak')
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_approved')
                    ->label('Zatwierdzony')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function (StaffVacationPeriod $record) {
                        $now = now()->toDateString();

                        if ($record->end_date->toDateString() < $now) {
                            return 'Zakończony';
                        } elseif ($record->start_date->toDateString() <= $now && $record->end_date->toDateString() >= $now) {
                            return 'Trwa';
                        } else {
                            return 'Zaplanowany';
                        }
                    })
                    ->color(function (StaffVacationPeriod $record) {
                        $now = now()->toDateString();

                        if ($record->end_date->toDateString() < $now) {
                            return 'gray';
                        } elseif ($record->start_date->toDateString() <= $now && $record->end_date->toDateString() >= $now) {
                            return 'success';
                        } else {
                            return 'info';
                        }
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pracownik')
                    ->relationship('user', 'first_name', function (Builder $query) {
                        $query->role('staff');
                    })
                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->name)
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_approved')
                    ->label('Status zatwierdzenia')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Tylko zatwierdzone')
                    ->falseLabel('Tylko oczekujące'),

                Tables\Filters\Filter::make('active')
                    ->label('Trwające teraz')
                    ->query(function (Builder $query) {
                        $today = now()->toDateString();
                        return $query->where('start_date', '<=', $today)
                                     ->where('end_date', '>=', $today);
                    }),

                Tables\Filters\Filter::make('upcoming')
                    ->label('Nadchodzące')
                    ->query(fn (Builder $query) => $query->where('start_date', '>', now()->toDateString())),

                Tables\Filters\Filter::make('past')
                    ->label('Zakończone')
                    ->query(fn (Builder $query) => $query->where('end_date', '<', now()->toDateString())),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Zatwierdź')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->hidden(fn (StaffVacationPeriod $record) => $record->is_approved)
                    ->action(fn (StaffVacationPeriod $record) => $record->update(['is_approved' => true]))
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve')
                        ->label('Zatwierdź zaznaczone')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each->update(['is_approved' => true]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('unapprove')
                        ->label('Cofnij zatwierdzenie')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->action(function ($records) {
                            $records->each->update(['is_approved' => false]);
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
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
            'index' => Pages\ListStaffVacationPeriods::route('/'),
            'create' => Pages\CreateStaffVacationPeriod::route('/create'),
            'edit' => Pages\EditStaffVacationPeriod::route('/{record}/edit'),
        ];
    }
}
