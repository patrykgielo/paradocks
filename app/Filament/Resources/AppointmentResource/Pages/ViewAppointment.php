<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\IconEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informacje o wizycie')
                    ->schema([
                        TextEntry::make('service.name')
                            ->label('Usługa'),
                        TextEntry::make('customer.full_name')
                            ->label('Klient'),
                        TextEntry::make('staff.full_name')
                            ->label('Pracownik'),
                        TextEntry::make('appointment_date')
                            ->label('Data wizyty')
                            ->date('d.m.Y'),
                        TextEntry::make('start_time')
                            ->label('Godzina rozpoczęcia')
                            ->time('H:i'),
                        TextEntry::make('end_time')
                            ->label('Godzina zakończenia')
                            ->time('H:i'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'confirmed' => 'success',
                                'cancelled' => 'danger',
                                'completed' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'pending' => 'Oczekująca',
                                'confirmed' => 'Potwierdzona',
                                'cancelled' => 'Anulowana',
                                'completed' => 'Zakończona',
                                default => $state,
                            }),
                        TextEntry::make('notes')
                            ->label('Notatki')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->notes),
                        TextEntry::make('cancellation_reason')
                            ->label('Powód anulowania')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->status === 'cancelled' && $record->cancellation_reason),
                    ])
                    ->columns(2),

                Section::make('Dane kontaktowe')
                    ->schema([
                        TextEntry::make('first_name')
                            ->label('Imię'),
                        TextEntry::make('last_name')
                            ->label('Nazwisko'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label('Telefon')
                            ->copyable(),
                        IconEntry::make('notify_email')
                            ->label('Powiadomienia Email')
                            ->boolean(),
                        IconEntry::make('notify_sms')
                            ->label('Powiadomienia SMS')
                            ->boolean(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Lokalizacja')
                    ->schema([
                        TextEntry::make('location_address')
                            ->label('Adres')
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('location_latitude')
                                    ->label('Szerokość'),
                                TextEntry::make('location_longitude')
                                    ->label('Długość'),
                            ])
                            ->visible(fn ($record) => $record->location_latitude && $record->location_longitude),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record->hasLocationData()),

                Section::make('Pojazd')
                    ->schema([
                        TextEntry::make('vehicleType.name')
                            ->label('Typ pojazdu')
                            ->visible(fn ($record) => $record->vehicleType),
                        TextEntry::make('vehicle_display')
                            ->label('Pojazd')
                            ->visible(fn ($record) => $record->vehicle_display),
                        TextEntry::make('vehicle_year')
                            ->label('Rocznik')
                            ->visible(fn ($record) => $record->vehicle_year),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->visible(fn ($record) => $record->vehicleType || $record->vehicle_display),

                Section::make('Dane do faktury')
                    ->schema([
                        IconEntry::make('invoice_requested')
                            ->label('Żądanie faktury')
                            ->boolean()
                            ->visible(fn ($record) => $record->invoice_requested),

                        TextEntry::make('invoice_type')
                            ->label('Typ faktury')
                            ->formatStateUsing(fn ($state) => match ($state) {
                                'individual' => 'Na osobę prywatną',
                                'company' => 'Faktura firmowa',
                                'foreign_eu' => 'Firma UE',
                                'foreign_non_eu' => 'Firma spoza UE',
                                default => '—',
                            })
                            ->visible(fn ($record) => $record->invoice_requested),

                        TextEntry::make('invoice_company_name')
                            ->label('Nazwa firmy')
                            ->visible(fn ($record) => $record->invoice_requested && $record->invoice_company_name),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('invoice_nip')
                                    ->label('NIP'),
                                TextEntry::make('invoice_regon')
                                    ->label('REGON'),
                            ])
                            ->visible(fn ($record) => $record->invoice_requested && $record->invoice_nip),

                        TextEntry::make('invoice_vat_id')
                            ->label('VAT ID (UE)')
                            ->visible(fn ($record) => $record->invoice_requested && $record->invoice_vat_id),

                        TextEntry::make('formatted_invoice_address')
                            ->label('Adres do faktury')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->invoice_requested),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->visible(fn ($record) => $record->invoice_requested),

                Section::make('Metadane')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Utworzono')
                            ->dateTime('d.m.Y H:i:s'),
                        TextEntry::make('updated_at')
                            ->label('Zaktualizowano')
                            ->dateTime('d.m.Y H:i:s'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
