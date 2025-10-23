<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Wizyty';

    protected static ?string $modelLabel = 'Wizyta';

    protected static ?string $pluralModelLabel = 'Wizyty';

    protected static ?string $navigationGroup = 'Service Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_id')
                    ->label('Usługa')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('end_time', null)),

                Forms\Components\Select::make('customer_id')
                    ->label('Klient')
                    ->relationship('customer', 'first_name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'customer'))
                    )
                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Imię')
                            ->required(),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nazwisko')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Hasło')
                            ->password()
                            ->required(),
                    ]),

                Forms\Components\Select::make('staff_id')
                    ->label('Pracownik')
                    ->relationship('staff', 'first_name', fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'staff'))
                    )
                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->full_name)
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive(),

                Forms\Components\DatePicker::make('appointment_date')
                    ->label('Data wizyty')
                    ->required()
                    ->native(false)
                    ->minDate(now())
                    ->reactive(),

                Forms\Components\TimePicker::make('start_time')
                    ->label('Czas rozpoczęcia')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $serviceId = $get('service_id');
                        if ($state && $serviceId) {
                            $service = Service::find($serviceId);
                            if ($service) {
                                $start = Carbon::parse($state);
                                $end = $start->copy()->addMinutes($service->duration_minutes);
                                $set('end_time', $end->format('H:i:s'));
                            }
                        }
                    }),

                Forms\Components\TimePicker::make('end_time')
                    ->label('Czas zakończenia')
                    ->required()
                    ->reactive(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Oczekująca',
                        'confirmed' => 'Potwierdzona',
                        'cancelled' => 'Anulowana',
                        'completed' => 'Zakończona',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false),

                Forms\Components\Textarea::make('notes')
                    ->label('Notatki')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('cancellation_reason')
                    ->label('Powód anulowania')
                    ->rows(3)
                    ->visible(fn (callable $get) => $get('status') === 'cancelled')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Lokalizacja')
                    ->schema([
                        Forms\Components\TextInput::make('location_address')
                            ->label('Adres')
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->readOnly(),
                        Forms\Components\TextInput::make('location_latitude')
                            ->label('Szerokość geograficzna')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('location_longitude')
                            ->label('Długość geograficzna')
                            ->numeric()
                            ->readOnly(),
                        Forms\Components\TextInput::make('location_place_id')
                            ->label('Google Place ID')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->readOnly(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Usługa')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.first_name')
                    ->label('Klient')
                    ->getStateUsing(fn ($record) => $record->customer?->full_name)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.first_name')
                    ->label('Pracownik')
                    ->getStateUsing(fn ($record) => $record->staff?->full_name)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_date')
                    ->label('Data')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Od')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Do')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('location_address')
                    ->label('Lokalizacja')
                    ->searchable()
                    ->limit(50)
                    ->placeholder('—')
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        return $column->getState();
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                        'secondary' => 'completed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Oczekująca',
                        'confirmed' => 'Potwierdzona',
                        'cancelled' => 'Anulowana',
                        'completed' => 'Zakończona',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('appointment_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Oczekująca',
                        'confirmed' => 'Potwierdzona',
                        'cancelled' => 'Anulowana',
                        'completed' => 'Zakończona',
                    ]),
                Tables\Filters\SelectFilter::make('service')
                    ->label('Usługa')
                    ->relationship('service', 'name'),
                Tables\Filters\SelectFilter::make('staff')
                    ->label('Pracownik')
                    ->relationship('staff', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->full_name),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
