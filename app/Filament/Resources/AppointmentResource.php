<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppointmentResource\Pages;
use App\Filament\Resources\AppointmentResource\RelationManagers;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;

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
                    ->relationship('customer', 'name', fn (Builder $query) =>
                        $query->whereHas('roles', fn ($q) => $q->where('name', 'customer'))
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->label('Imię i nazwisko')
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
                    ->relationship('staff', 'name', fn (Builder $query) =>
                        $query->whereHas('roles', fn ($q) => $q->where('name', 'staff'))
                    )
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
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Klient')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('staff.name')
                    ->label('Pracownik')
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
                    ->relationship('staff', 'name'),
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
