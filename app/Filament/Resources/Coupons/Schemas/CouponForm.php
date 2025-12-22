<?php

declare(strict_types=1);

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->label('Kod kuponu')
                            ->placeholder('np. PD-A3F9K2')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Unikalny kod, który klient wpisuje przy rezerwacji. Pozostaw puste dla auto-generacji.')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Typ kuponu')
                            ->options([
                                'manual' => 'Ręczny - stworzony przez admina',
                                'auto_service' => 'Auto (usługa) - generowany po wykonaniu usługi',
                                'auto_amount' => 'Auto (kwota) - generowany po przekroczeniu kwoty',
                            ])
                            ->required()
                            ->default('manual')
                            ->live()
                            ->native(false)
                            ->helperText('Typ określa sposób generowania kuponu'),

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
                            ->label(fn (callable $get) => $get('discount_type') === 'percentage' ? 'Wartość rabatu (%)' : 'Wartość rabatu (PLN)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(fn (callable $get) => $get('discount_type') === 'percentage' ? 100 : null)
                            ->suffix(fn (callable $get) => $get('discount_type') === 'percentage' ? '%' : 'PLN')
                            ->helperText(fn (callable $get) => $get('discount_type') === 'percentage' ? 'Wartość od 0 do 100' : 'Kwota w złotych'),
                    ])
                    ->columns(2),

                Section::make('Warunki auto-generacji')
                    ->schema([
                        Forms\Components\Select::make('condition_service_id')
                            ->label('Usługa warunkująca')
                            ->relationship('conditionService', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Kupon zostanie wygenerowany po wykonaniu tej usługi')
                            ->visible(fn (callable $get) => $get('type') === 'auto_service')
                            ->required(fn (callable $get) => $get('type') === 'auto_service'),

                        Forms\Components\TextInput::make('condition_min_amount')
                            ->label('Minimalna kwota (PLN)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('PLN')
                            ->helperText('Kupon zostanie wygenerowany po przekroczeniu tej kwoty')
                            ->visible(fn (callable $get) => $get('type') === 'auto_amount')
                            ->required(fn (callable $get) => $get('type') === 'auto_amount'),
                    ])
                    ->columns(2)
                    ->visible(fn (callable $get) => in_array($get('type'), ['auto_service', 'auto_amount'])),

                Section::make('Ważność i limity')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktywny')
                            ->default(true)
                            ->helperText('Czy kupon może być obecnie wykorzystywany'),

                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label('Ważny od')
                            ->native(false)
                            ->helperText('Data rozpoczęcia ważności (puste = od razu)'),

                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Ważny do')
                            ->native(false)
                            ->helperText('Data wygaśnięcia (puste = bezterminowo)'),

                        Forms\Components\TextInput::make('max_uses')
                            ->label('Maksymalna liczba użyć')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Ile razy kupon może być użyty (puste = bez limitu)'),
                    ])
                    ->columns(2),

                Section::make('Influencer')
                    ->schema([
                        Forms\Components\Select::make('influencer_id')
                            ->label('Influencer / Partner')
                            ->relationship('influencer', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Opcjonalne: przypisz kupon do influencera/partnera')
                            ->columnSpanFull(),
                    ])
                    ->collapsed()
                    ->visible(fn (callable $get) => $get('type') === 'manual'),

                Section::make('Statystyki (tylko odczyt)')
                    ->schema([
                        Forms\Components\TextInput::make('uses_count')
                            ->label('Liczba użyć')
                            ->disabled()
                            ->suffix('razy'),

                        Forms\Components\TextInput::make('total_discount_given')
                            ->label('Łączna wartość rabatów')
                            ->disabled()
                            ->suffix('PLN'),

                        Forms\Components\TextInput::make('generated_bookings_count')
                            ->label('Wygenerowane rezerwacje')
                            ->disabled()
                            ->suffix('rezerwacji'),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->hiddenOn('create'),
            ]);
    }
}
