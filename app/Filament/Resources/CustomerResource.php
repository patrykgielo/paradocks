<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static string | UnitEnum | null $navigationGroup = 'Zarządzanie Użytkownikami';

    protected static ?string $modelLabel = 'Klient';

    protected static ?string $pluralModelLabel = 'Klienci';

    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('roles', function ($query) {
            $query->where('name', 'customer');
        });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
                Section::make('Dane osobowe')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Imię')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Jan'),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nazwisko')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Kowalski'),
                    ])->columns(2),

                Section::make('Kontakt')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('jan.kowalski@example.com'),
                        Forms\Components\TextInput::make('phone_e164')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+48501234567')
                            ->helperText('Format międzynarodowy E.164, np. +48501234567')
                            ->regex('/^\+\d{1,3}\d{6,14}$/'),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email zweryfikowany')
                            ->displayFormat('d.m.Y H:i'),
                    ])->columns(2),

                Section::make('Adres')
                    ->schema([
                        Forms\Components\TextInput::make('street_name')
                            ->label('Ulica')
                            ->maxLength(255)
                            ->placeholder('Marszałkowska'),
                        Forms\Components\TextInput::make('street_number')
                            ->label('Numer')
                            ->maxLength(20)
                            ->placeholder('12/34'),
                        Forms\Components\TextInput::make('city')
                            ->label('Miasto')
                            ->maxLength(255)
                            ->placeholder('Warszawa'),
                        Forms\Components\TextInput::make('postal_code')
                            ->label('Kod pocztowy')
                            ->maxLength(10)
                            ->placeholder('00-000')
                            ->mask('99-999')
                            ->regex('/^\d{2}-\d{3}$/'),
                        Forms\Components\Textarea::make('access_notes')
                            ->label('Informacje o dostępie')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Dodatkowe informacje o adresie, np. kod do bramy, piętro...')
                            ->columnSpanFull(),
                    ])->columns(4)->collapsible(),

                Section::make('Hasło')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Hasło')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->revealable()
                            ->helperText('Pozostaw puste, aby zachować obecne hasło'),
                    ]),

                Section::make('Rola')
                    ->schema([
                        Forms\Components\Hidden::make('role')
                            ->default('customer'),
                        Forms\Components\Placeholder::make('role_info')
                            ->label('Rola użytkownika')
                            ->content('Ten użytkownik będzie miał automatycznie przypisaną rolę: Klient')
                            ->helperText('Aby zmienić rolę użytkownika, przejdź do sekcji Użytkownicy'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Imię')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nazwisko')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone_e164')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('city')
                    ->label('Miasto')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('customerAppointments_count')
                    ->label('Liczba rezerwacji')
                    ->counts('customerAppointments')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Zweryfikowany')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data rejestracji')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email zweryfikowany')
                    ->nullable()
                    ->placeholder('Wszystkie')
                    ->trueLabel('Zweryfikowane')
                    ->falseLabel('Niezweryfikowane'),
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
                        ->label('Usuń zaznaczonych'),
                ]),
            ])
            ->emptyStateHeading('Brak klientów')
            ->emptyStateDescription('Dodaj pierwszego klienta klikając przycisk poniżej.')
            ->emptyStateIcon('heroicon-o-user-group');
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
