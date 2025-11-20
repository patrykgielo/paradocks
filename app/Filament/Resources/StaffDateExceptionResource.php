<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\LocalizedDatePicker;
use App\Filament\Forms\Components\LocalizedTimePicker;
use App\Filament\Resources\StaffDateExceptionResource\Pages;
use App\Filament\Resources\StaffDateExceptionResource\RelationManagers;
use App\Filament\Tables\Columns\LocalizedDateColumn;
use App\Filament\Tables\Columns\LocalizedDateTimeColumn;
use App\Filament\Tables\Columns\LocalizedTimeColumn;
use App\Models\StaffDateException;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StaffDateExceptionResource extends Resource
{
    protected static ?string $model = StaffDateException::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Harmonogramy';

    protected static ?string $modelLabel = 'Wyjątek';

    protected static ?string $pluralModelLabel = 'Wyjątki od harmonogramu';

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

                        LocalizedDatePicker::make('exception_date')
                            ->label('Data wyjątku')
                            ->required()
                            ->helperText('Dzień, na który chcesz zastosować wyjątek'),
                    ])->columns(2),

                Forms\Components\Section::make('Typ wyjątku')
                    ->schema([
                        Forms\Components\Radio::make('exception_type')
                            ->label('Co się dzieje w tym dniu?')
                            ->options([
                                StaffDateException::TYPE_UNAVAILABLE => 'Niedostępny (dzień wolny, wizyta lekarska, choroba)',
                                StaffDateException::TYPE_AVAILABLE => 'Dostępny (pracuje w normalnie wolny dzień)',
                            ])
                            ->descriptions([
                                StaffDateException::TYPE_UNAVAILABLE => 'Pracownik nie będzie dostępny w tym dniu',
                                StaffDateException::TYPE_AVAILABLE => 'Pracownik będzie dostępny mimo że normalnie nie pracuje',
                            ])
                            ->required()
                            ->reactive()
                            ->default(StaffDateException::TYPE_UNAVAILABLE),
                    ]),

                Forms\Components\Section::make('Zakres czasowy')
                    ->description('Opcjonalne: jeśli nie wypełnisz, wyjątek dotyczy całego dnia')
                    ->schema([
                        LocalizedTimePicker::make('start_time')
                            ->label('Od godziny')
                            ->helperText('Zostaw puste jeśli dotyczy całego dnia'),

                        LocalizedTimePicker::make('end_time')
                            ->label('Do godziny')
                            ->after('start_time')
                            ->helperText('Zostaw puste jeśli dotyczy całego dnia'),
                    ])->columns(2),

                Forms\Components\Section::make('Dodatkowe informacje')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('Powód (opcjonalnie)')
                            ->rows(3)
                            ->placeholder('np. "Wizyta u lekarza", "Dzień wolny", "Święto"')
                            ->columnSpanFull(),
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

                LocalizedDateColumn::make('exception_date')
                    ->label('Data')
                    ->sortable()
                    ->badge()
                    ->color(fn (StaffDateException $record) =>
                        $record->exception_date->isPast() ? 'gray' : 'info'
                    ),

                Tables\Columns\TextColumn::make('exception_type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match($state) {
                        StaffDateException::TYPE_UNAVAILABLE => 'Niedostępny',
                        StaffDateException::TYPE_AVAILABLE => 'Dostępny',
                        default => $state,
                    })
                    ->color(fn (string $state) => match($state) {
                        StaffDateException::TYPE_UNAVAILABLE => 'danger',
                        StaffDateException::TYPE_AVAILABLE => 'success',
                        default => 'gray',
                    }),

                LocalizedTimeColumn::make('start_time')
                    ->label('Od')
                    ->placeholder('Cały dzień')
                    ->toggleable(),

                LocalizedTimeColumn::make('end_time')
                    ->label('Do')
                    ->placeholder('Cały dzień')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Powód')
                    ->limit(40)
                    ->placeholder('Brak')
                    ->toggleable(),

                LocalizedDateTimeColumn::make('created_at')
                    ->label('Utworzono')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('exception_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pracownik')
                    ->relationship('user', 'first_name', function (Builder $query) {
                        $query->role('staff');
                    })
                    ->getOptionLabelFromRecordUsing(fn (User $record) => $record->name)
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('exception_type')
                    ->label('Typ wyjątku')
                    ->options([
                        StaffDateException::TYPE_UNAVAILABLE => 'Niedostępny',
                        StaffDateException::TYPE_AVAILABLE => 'Dostępny',
                    ]),

                Tables\Filters\Filter::make('future')
                    ->label('Tylko przyszłe')
                    ->query(fn (Builder $query) => $query->where('exception_date', '>=', now()->toDateString())),

                Tables\Filters\Filter::make('past')
                    ->label('Tylko przeszłe')
                    ->query(fn (Builder $query) => $query->where('exception_date', '<', now()->toDateString())),
            ])
            ->actions([
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
            'index' => Pages\ListStaffDateExceptions::route('/'),
            'create' => Pages\CreateStaffDateException::route('/create'),
            'edit' => Pages\EditStaffDateException::route('/{record}/edit'),
        ];
    }
}
