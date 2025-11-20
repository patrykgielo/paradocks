<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceAvailabilityResource\Pages;
use App\Filament\Resources\ServiceAvailabilityResource\RelationManagers;
use App\Models\ServiceAvailability;
use App\Models\Service;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceAvailabilityResource extends Resource
{
    protected static ?string $model = ServiceAvailability::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Harmonogramy';

    protected static ?string $modelLabel = 'Dostępność';

    protected static ?string $pluralModelLabel = 'Dostępności Pracowników';

    protected static ?int $navigationSort = 3;

    // DEPRECATED: Hidden from navigation 2025-11-20 (UI-MIGRATION-001)
    // LEGACY system (pre-Option B migration)
    // Data migrated to staff_schedules + service_staff tables
    // Keep for historical reference only
    // TODO: Consider removal after 30 days if no issues (2025-12-20)
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacje o dostępności')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Pracownik')
                            ->relationship(
                                'user',
                                'first_name',
                                fn (Builder $query) => $query->role('staff')
                            )
                            ->getOptionLabelFromRecordUsing(fn (User $record) => $record->name)
                            ->searchable(['first_name', 'last_name', 'email'])
                            ->required()
                            ->preload()
                            ->helperText('Wybierz pracownika z listy'),

                        Forms\Components\Select::make('service_id')
                            ->label('Usługa')
                            ->relationship('service', 'name')
                            ->searchable()
                            ->required()
                            ->preload()
                            ->helperText('Wybierz usługę, którą pracownik może wykonywać'),
                    ])->columns(2),

                Forms\Components\Section::make('Harmonogram')
                    ->schema([
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
                                fn ($get, $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    $userId = $get('user_id');
                                    $serviceId = $get('service_id');
                                    $dayOfWeek = $get('day_of_week');
                                    $startTime = $get('start_time');

                                    if (!$userId || !$serviceId || $dayOfWeek === null || !$startTime) {
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
                                        $fail('Ten przedział czasowy nakłada się z istniejącą dostępnością dla tego pracownika, usługi i dnia.');
                                    }
                                },
                            ]),
                    ])->columns(3),
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data dodania')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pracownik')
                    ->relationship(
                        'user',
                        'first_name',
                        fn (Builder $query) => $query->role('staff')
                    )
                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->name)
                    ->searchable()
                    ->preload(),

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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edytuj'),
                Tables\Actions\DeleteAction::make()
                    ->label('Usuń'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Usuń zaznaczone'),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('setStandardSchedule')
                    ->label('Ustaw standardowy harmonogram')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->form([
                        Forms\Components\Section::make('Wybierz pracowników')
                            ->schema([
                                Forms\Components\Select::make('user_ids')
                                    ->label('Pracownicy')
                                    ->multiple()
                                    ->options(User::role('staff')->get()->mapWithKeys(fn ($user) => [$user->id => $user->name]))
                                    ->searchable()
                                    ->required()
                                    ->helperText('Wybierz pracowników, dla których chcesz ustawić harmonogram')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Dni tygodnia')
                            ->schema([
                                Forms\Components\CheckboxList::make('days')
                                    ->label('Wybierz dni')
                                    ->options([
                                        1 => 'Poniedziałek',
                                        2 => 'Wtorek',
                                        3 => 'Środa',
                                        4 => 'Czwartek',
                                        5 => 'Piątek',
                                        6 => 'Sobota',
                                        0 => 'Niedziela',
                                    ])
                                    ->columns(4)
                                    ->default([1, 2, 3, 4, 5])
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Godziny pracy')
                            ->schema([
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Godzina rozpoczęcia')
                                    ->required()
                                    ->seconds(false)
                                    ->minutesStep(15)
                                    ->default('09:00'),

                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Godzina zakończenia')
                                    ->required()
                                    ->seconds(false)
                                    ->minutesStep(15)
                                    ->default('17:00')
                                    ->after('start_time'),
                            ])->columns(2),

                        Forms\Components\Section::make('Usługi')
                            ->schema([
                                Forms\Components\Select::make('service_ids')
                                    ->label('Usługi')
                                    ->multiple()
                                    ->options(Service::pluck('name', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->helperText('Wybierz usługi, które pracownicy będą mogli wykonywać')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Opcje')
                            ->schema([
                                Forms\Components\Toggle::make('delete_existing')
                                    ->label('Usuń istniejące dostępności przed dodaniem nowych')
                                    ->helperText('Jeśli zaznaczone, wszystkie istniejące dostępności dla wybranych pracowników zostaną usunięte')
                                    ->default(false),
                            ]),
                    ])
                    ->action(function (array $data) {
                        // Walidacja wymaganych pól
                        if (empty($data['user_ids'] ?? [])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Błąd walidacji')
                                ->body('Musisz wybrać co najmniej jednego pracownika')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (empty($data['service_ids'] ?? [])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Błąd walidacji')
                                ->body('Musisz wybrać co najmniej jedną usługę')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (empty($data['days'] ?? [])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Błąd walidacji')
                                ->body('Musisz wybrać co najmniej jeden dzień')
                                ->danger()
                                ->send();
                            return;
                        }

                        $created = 0;
                        $deleted = 0;

                        foreach ($data['user_ids'] as $userId) {
                            // Delete existing availabilities if requested
                            if (!empty($data['delete_existing'])) {
                                $deleted += ServiceAvailability::where('user_id', $userId)->delete();
                            }

                            // Create new availabilities
                            foreach ($data['service_ids'] as $serviceId) {
                                foreach ($data['days'] as $dayOfWeek) {
                                    ServiceAvailability::create([
                                        'user_id' => $userId,
                                        'service_id' => $serviceId,
                                        'day_of_week' => $dayOfWeek,
                                        'start_time' => $data['start_time'],
                                        'end_time' => $data['end_time'],
                                    ]);
                                    $created++;
                                }
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Harmonogram został ustawiony')
                            ->body("Utworzono {$created} dostępności" . ($deleted > 0 ? ", usunięto {$deleted} starych" : ""))
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Ustaw standardowy harmonogram pracy')
                    ->modalDescription('To narzędzie pozwala szybko ustawić harmonogram pracy dla wielu pracowników na raz.')
                    ->modalSubmitActionLabel('Ustaw harmonogram')
                    ->modalWidth('3xl'),

                Tables\Actions\Action::make('copySchedule')
                    ->label('Kopiuj harmonogram')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->form([
                        Forms\Components\Section::make('Pracownik źródłowy')
                            ->description('Wybierz pracownika, którego harmonogram chcesz skopiować')
                            ->schema([
                                Forms\Components\Select::make('source_user_id')
                                    ->label('Pracownik')
                                    ->options(User::role('staff')->get()->mapWithKeys(fn ($user) => [$user->id => $user->name]))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state) {
                                            $count = ServiceAvailability::where('user_id', $state)->count();
                                            $set('source_availabilities_count', $count);
                                        }
                                    })
                                    ->helperText(fn ($get) => $get('source_availabilities_count')
                                        ? "Ten pracownik ma {$get('source_availabilities_count')} dostępności"
                                        : 'Wybierz pracownika'
                                    )
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Pracownicy docelowi')
                            ->description('Wybierz pracowników, którym chcesz skopiować harmonogram')
                            ->schema([
                                Forms\Components\Select::make('target_user_ids')
                                    ->label('Pracownicy')
                                    ->multiple()
                                    ->options(function ($get) {
                                        $sourceUserId = $get('source_user_id');
                                        return User::role('staff')
                                            ->when($sourceUserId, fn ($query) => $query->where('id', '!=', $sourceUserId))
                                            ->get()
                                            ->mapWithKeys(fn ($user) => [$user->id => $user->name]);
                                    })
                                    ->searchable()
                                    ->required()
                                    ->helperText('Możesz wybrać wielu pracowników naraz')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Opcje kopiowania')
                            ->schema([
                                Forms\Components\Toggle::make('delete_existing')
                                    ->label('Usuń istniejące dostępności pracowników docelowych')
                                    ->helperText('Jeśli zaznaczone, wszystkie dostępności pracowników docelowych zostaną usunięte przed skopiowaniem')
                                    ->default(false),

                                Forms\Components\Toggle::make('copy_all_services')
                                    ->label('Kopiuj dla wszystkich usług')
                                    ->helperText('Jeśli wyłączone, możesz wybrać konkretne usługi do skopiowania')
                                    ->default(true)
                                    ->live(),

                                Forms\Components\Select::make('service_ids')
                                    ->label('Wybierz usługi')
                                    ->multiple()
                                    ->options(Service::pluck('name', 'id'))
                                    ->searchable()
                                    ->visible(fn ($get) => !$get('copy_all_services'))
                                    ->requiredIf('copy_all_services', false)
                                    ->helperText('Tylko wybrane usługi zostaną skopiowane')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->action(function (array $data) {
                        // Walidacja wymaganych pól
                        if (empty($data['source_user_id'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Błąd walidacji')
                                ->body('Musisz wybrać pracownika źródłowego')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (empty($data['target_user_ids'])) {
                            \Filament\Notifications\Notification::make()
                                ->title('Błąd walidacji')
                                ->body('Musisz wybrać co najmniej jednego pracownika docelowego')
                                ->danger()
                                ->send();
                            return;
                        }

                        $sourceUserId = $data['source_user_id'];
                        $targetUserIds = $data['target_user_ids'];
                        $created = 0;
                        $deleted = 0;

                        // Get source availabilities
                        $sourceAvailabilities = ServiceAvailability::where('user_id', $sourceUserId)
                            ->when(!empty($data['copy_all_services']) === false && isset($data['service_ids']), function ($query) use ($data) {
                                $query->whereIn('service_id', $data['service_ids']);
                            })
                            ->get();

                        if ($sourceAvailabilities->isEmpty()) {
                            \Filament\Notifications\Notification::make()
                                ->title('Brak dostępności do skopiowania')
                                ->warning()
                                ->send();
                            return;
                        }

                        foreach ($targetUserIds as $targetUserId) {
                            // Delete existing if requested
                            if (!empty($data['delete_existing'])) {
                                $deleted += ServiceAvailability::where('user_id', $targetUserId)->delete();
                            }

                            // Copy availabilities
                            foreach ($sourceAvailabilities as $availability) {
                                ServiceAvailability::create([
                                    'user_id' => $targetUserId,
                                    'service_id' => $availability->service_id,
                                    'day_of_week' => $availability->day_of_week,
                                    'start_time' => $availability->start_time,
                                    'end_time' => $availability->end_time,
                                ]);
                                $created++;
                            }
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Harmonogram został skopiowany')
                            ->body("Skopiowano {$created} dostępności dla " . count($targetUserIds) . " pracowników" . ($deleted > 0 ? ", usunięto {$deleted} starych" : ""))
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Kopiuj harmonogram między pracownikami')
                    ->modalDescription('Skopiuj harmonogram pracy z jednego pracownika na innych.')
                    ->modalSubmitActionLabel('Kopiuj harmonogram')
                    ->modalWidth('2xl'),
            ])
            ->emptyStateHeading('Brak dostępności')
            ->emptyStateDescription('Dodaj pierwszą dostępność pracownika klikając przycisk poniżej.')
            ->emptyStateIcon('heroicon-o-calendar')
            ->defaultSort('user_id', 'asc');
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
            'index' => Pages\ListServiceAvailabilities::route('/'),
            'create' => Pages\CreateServiceAvailability::route('/create'),
            'edit' => Pages\EditServiceAvailability::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
