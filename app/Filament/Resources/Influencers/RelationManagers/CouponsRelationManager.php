<?php

declare(strict_types=1);

namespace App\Filament\Resources\Influencers\RelationManagers;

use App\Models\Coupon;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CouponsRelationManager extends RelationManager
{
    protected static string $relationship = 'coupons';

    protected static ?string $recordTitleAttribute = 'code';

    protected static ?string $title = 'Kupony influencera';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kod kuponu')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('discount_type')
                            ->label('Typ rabatu')
                            ->options([
                                'percentage' => 'Procentowy (%)',
                                'fixed' => 'Stała kwota (PLN)',
                            ])
                            ->required()
                            ->default('percentage')
                            ->native(false)
                            ->live(),

                        Forms\Components\TextInput::make('discount_value')
                            ->label(fn (callable $get) => $get('discount_type') === 'percentage' ? 'Wartość (%)' : 'Wartość (PLN)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn (callable $get) => $get('discount_type') === 'percentage' ? 100 : null)
                            ->suffix(fn (callable $get) => $get('discount_type') === 'percentage' ? '%' : 'PLN'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywny')
                            ->default(true),

                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Ważny do')
                            ->native(false),

                        Forms\Components\TextInput::make('max_uses')
                            ->label('Max użyć')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Puste = bez limitu'),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kod')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('discount')
                    ->label('Rabat')
                    ->getStateUsing(fn (Coupon $record) => $record->formatted_discount),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktywny')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\TextColumn::make('uses_count')
                    ->label('Użycia')
                    ->getStateUsing(function (Coupon $record): string {
                        $used = $record->uses_count;
                        $max = $record->max_uses ?? '∞';

                        return "{$used} / {$max}";
                    }),

                Tables\Columns\TextColumn::make('total_discount_given')
                    ->label('Łączny rabat')
                    ->money('PLN'),

                Tables\Columns\TextColumn::make('generated_bookings_count')
                    ->label('Rezerwacje')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Wygasa')
                    ->date('d.m.Y')
                    ->placeholder('Bezterminowo'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Tylko aktywne')
                    ->falseLabel('Tylko nieaktywne'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj kupon')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = 'manual'; // Influencer coupons are always manual
                        $data['influencer_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ])
            ->recordActions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Brak kuponów')
            ->emptyStateDescription('Utwórz pierwszy kupon dla tego influencera')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj kupon')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['type'] = 'manual';
                        $data['influencer_id'] = $this->getOwnerRecord()->id;

                        return $data;
                    }),
            ]);
    }
}
