<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StaffDateExceptionResource\Pages;
use App\Filament\Resources\StaffDateExceptionResource\RelationManagers;
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

                        Forms\Components\DatePicker::make('exception_date')
                            ->label('Data wyjątku')
                            ->native(false)
                            ->displayFormat('Y-m-d')
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
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Od godziny')
                            ->seconds(false)
                            ->helperText('Zostaw puste jeśli dotyczy całego dnia'),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Do godziny')
                            ->seconds(false)
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

                Tables\Columns\TextColumn::make('exception_date')
                    ->label('Data')
                    ->date('Y-m-d')
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

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Od')
                    ->time('H:i')
                    ->placeholder('Cały dzień')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Do')
                    ->time('H:i')
                    ->placeholder('Cały dzień')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Powód')
                    ->limit(40)
                    ->placeholder('Brak')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('Y-m-d H:i')
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
