<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class EmployeeResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'Zarządzanie Użytkownikami';

    protected static ?string $modelLabel = 'Pracownik';

    protected static ?string $pluralModelLabel = 'Pracownicy';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('roles', function ($query) {
            $query->where('name', 'staff');
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informacje podstawowe')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Imię i nazwisko')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email zweryfikowany')
                            ->displayFormat('d.m.Y H:i'),
                    ])->columns(2),

                Forms\Components\Section::make('Hasło')
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

                Forms\Components\Section::make('Rola')
                    ->schema([
                        Forms\Components\Hidden::make('role')
                            ->default('staff'),
                        Forms\Components\Placeholder::make('role_info')
                            ->label('Rola użytkownika')
                            ->content('Ten użytkownik będzie miał automatycznie przypisaną rolę: Pracownik (Staff)')
                            ->helperText('Aby zmienić rolę użytkownika, przejdź do sekcji Użytkownicy'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Imię i nazwisko')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('staffAppointments_count')
                    ->label('Liczba wizyt')
                    ->counts('staffAppointments')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('serviceAvailabilities_count')
                    ->label('Dostępności')
                    ->counts('serviceAvailabilities')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Zweryfikowany')
                    ->boolean()
                    ->sortable()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data dodania')
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
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edytuj'),
                Tables\Actions\DeleteAction::make()
                    ->label('Usuń'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Usuń zaznaczonych'),
                ]),
            ])
            ->emptyStateHeading('Brak pracowników')
            ->emptyStateDescription('Dodaj pierwszego pracownika klikając przycisk poniżej.')
            ->emptyStateIcon('heroicon-o-user-circle');
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
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getEloquentQuery()->count();
    }
}
