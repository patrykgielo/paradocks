<?php

declare(strict_types=1);

namespace App\Filament\Resources\Influencers\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InfluencerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dane influencera')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nazwa / Imię i nazwisko')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('np. Jan Kowalski - AutoBlog.pl')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+48 500 100 200'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notatki')
                            ->rows(4)
                            ->placeholder('Informacje o partnerstwie, umowie, warunkach współpracy...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Statystyki (tylko odczyt)')
                    ->schema([
                        Forms\Components\Placeholder::make('total_coupons')
                            ->label('Liczba kuponów')
                            ->content(fn ($record) => $record ? $record->coupons()->count() : 0),

                        Forms\Components\Placeholder::make('total_discount_given')
                            ->label('Łączna wartość rabatów')
                            ->content(fn ($record) => $record ? number_format($record->coupons()->sum('total_discount_given'), 2).' PLN' : '0.00 PLN'),

                        Forms\Components\Placeholder::make('total_bookings')
                            ->label('Wygenerowane rezerwacje')
                            ->content(fn ($record) => $record ? $record->coupons()->sum('generated_bookings_count') : 0),
                    ])
                    ->columns(3)
                    ->collapsed()
                    ->hiddenOn('create'),
            ]);
    }
}
