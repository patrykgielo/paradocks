<?php

namespace App\Filament\Pages;

use App\Support\Settings\SettingsManager;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'System Settings';

    protected static string $view = 'filament.pages.system-settings';

    protected static ?string $slug = 'system-settings';

    protected static ?string $title = 'System Settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage settings') ?? false;
    }

    protected function getFormDefaults(): array
    {
        $settings = app(SettingsManager::class);

        $booking = $settings->bookingConfiguration();
        $map = $settings->mapConfiguration();
        $contact = $settings->contactInformation();
        $marketing = $settings->marketingContent();

        return [
            'booking' => $booking,
            'map' => $map,
            'contact' => $contact,
            'marketing' => [
                'hero_title' => $marketing['hero_title'] ?? '',
                'hero_subtitle' => $marketing['hero_subtitle'] ?? '',
                'services_heading' => $marketing['services_heading'] ?? '',
                'services_subheading' => $marketing['services_subheading'] ?? '',
                'features_heading' => $marketing['features_heading'] ?? '',
                'features_subheading' => $marketing['features_subheading'] ?? '',
                'feature_one_title' => $marketing['features'][0]['title'] ?? '',
                'feature_one_description' => $marketing['features'][0]['description'] ?? '',
                'feature_two_title' => $marketing['features'][1]['title'] ?? '',
                'feature_two_description' => $marketing['features'][1]['description'] ?? '',
                'feature_three_title' => $marketing['features'][2]['title'] ?? '',
                'feature_three_description' => $marketing['features'][2]['description'] ?? '',
                'cta_heading' => $marketing['cta_heading'] ?? '',
                'cta_subheading' => $marketing['cta_subheading'] ?? '',
                'important_info_heading' => $marketing['important_info_heading'] ?? '',
                'important_info_points' => implode(PHP_EOL, $marketing['important_info_points'] ?? []),
            ],
        ];
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->form->fill($this->getFormDefaults());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Harmonogram rezerwacji')
                    ->schema([
                        Grid::make(3)->schema([
                            TimePicker::make('booking.business_hours_start')
                                ->label('Godzina rozpoczęcia')
                                ->seconds(false)
                                ->required(),
                            TimePicker::make('booking.business_hours_end')
                                ->label('Godzina zakończenia')
                                ->seconds(false)
                                ->required(),
                            TextInput::make('booking.slot_interval_minutes')
                                ->label('Interwał slotów (min)')
                                ->numeric()
                                ->minValue(5)
                                ->maxValue(180)
                                ->required(),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('booking.advance_booking_hours')
                                ->label('Minimalne wyprzedzenie (h)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(168)
                                ->required(),
                            TextInput::make('booking.cancellation_hours')
                                ->label('Limit anulacji (h)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(168)
                                ->required(),
                            TextInput::make('booking.max_service_duration_minutes')
                                ->label('Maksymalny czas usługi (min)')
                                ->numeric()
                                ->minValue(30)
                                ->maxValue(1440)
                                ->required(),
                        ]),
                    ])->columns(1),
                Section::make('Mapa i lokalizacja')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('map.default_latitude')
                                ->label('Domyślna szerokość')
                                ->numeric()
                                ->step(0.000001)
                                ->required(),
                            TextInput::make('map.default_longitude')
                                ->label('Domyślna długość')
                                ->numeric()
                                ->step(0.000001)
                                ->required(),
                            TextInput::make('map.default_zoom')
                                ->label('Poziom przybliżenia')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(20)
                                ->required(),
                            TextInput::make('map.country_code')
                                ->label('Kod kraju (ISO)')
                                ->maxLength(2)
                                ->required(),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('map.map_id')
                                ->label('Google Map ID')
                                ->helperText('Pozostaw puste aby użyć wartości z konfiguracji .env'),
                            Toggle::make('map.debug_panel_enabled')
                                ->label('Panel debugowania mapy')
                                ->inline(false),
                        ]),
                    ])->columns(1),
                Section::make('Dane kontaktowe')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('contact.email')
                                ->label('Adres e-mail')
                                ->email()
                                ->required(),
                            TextInput::make('contact.phone')
                                ->label('Telefon kontaktowy')
                                ->required(),
                            TextInput::make('contact.address_line')
                                ->label('Ulica i numer')
                                ->required(),
                            TextInput::make('contact.city')
                                ->label('Miasto')
                                ->required(),
                            TextInput::make('contact.postal_code')
                                ->label('Kod pocztowy')
                                ->required(),
                        ]),
                    ])->columns(1),
                Section::make('Treści marketingowe')
                    ->schema([
                        TextInput::make('marketing.hero_title')
                            ->label('Hero - tytuł')
                            ->required(),
                        Textarea::make('marketing.hero_subtitle')
                            ->label('Hero - opis')
                            ->rows(3)
                            ->required(),
                        TextInput::make('marketing.services_heading')
                            ->label('Sekcja usług - tytuł')
                            ->required(),
                        Textarea::make('marketing.services_subheading')
                            ->label('Sekcja usług - opis')
                            ->rows(3)
                            ->required(),
                        TextInput::make('marketing.features_heading')
                            ->label('Sekcja korzyści - tytuł')
                            ->required(),
                        Textarea::make('marketing.features_subheading')
                            ->label('Sekcja korzyści - opis')
                            ->rows(3)
                            ->required(),
                        Grid::make(3)->schema([
                            TextInput::make('marketing.feature_one_title')
                                ->label('Korzyść 1 - tytuł')
                                ->required(),
                            TextInput::make('marketing.feature_two_title')
                                ->label('Korzyść 2 - tytuł')
                                ->required(),
                            TextInput::make('marketing.feature_three_title')
                                ->label('Korzyść 3 - tytuł')
                                ->required(),
                        ]),
                        Grid::make(3)->schema([
                            Textarea::make('marketing.feature_one_description')
                                ->label('Korzyść 1 - opis')
                                ->rows(3)
                                ->required(),
                            Textarea::make('marketing.feature_two_description')
                                ->label('Korzyść 2 - opis')
                                ->rows(3)
                                ->required(),
                            Textarea::make('marketing.feature_three_description')
                                ->label('Korzyść 3 - opis')
                                ->rows(3)
                                ->required(),
                        ]),
                        TextInput::make('marketing.cta_heading')
                            ->label('CTA - tytuł')
                            ->required(),
                        Textarea::make('marketing.cta_subheading')
                            ->label('CTA - opis')
                            ->rows(3)
                            ->required(),
                        TextInput::make('marketing.important_info_heading')
                            ->label('Sekcja informacji - tytuł')
                            ->required(),
                        Textarea::make('marketing.important_info_points')
                            ->label('Punkty informacji (każdy w osobnej linii)')
                            ->rows(4)
                            ->helperText('Każda linia zostanie wyświetlona jako osobny punkt listy'),
                    ])->columns(1),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $state = $this->form->getState();

        $booking = [
            'business_hours_start' => $state['booking']['business_hours_start'],
            'business_hours_end' => $state['booking']['business_hours_end'],
            'slot_interval_minutes' => (int) $state['booking']['slot_interval_minutes'],
            'advance_booking_hours' => (int) $state['booking']['advance_booking_hours'],
            'cancellation_hours' => (int) $state['booking']['cancellation_hours'],
            'max_service_duration_minutes' => (int) $state['booking']['max_service_duration_minutes'],
        ];

        $map = [
            'default_latitude' => (float) $state['map']['default_latitude'],
            'default_longitude' => (float) $state['map']['default_longitude'],
            'default_zoom' => (int) $state['map']['default_zoom'],
            'country_code' => strtolower($state['map']['country_code']),
            'map_id' => $state['map']['map_id'] ?: null,
            'debug_panel_enabled' => (bool) $state['map']['debug_panel_enabled'],
        ];

        $contact = [
            'email' => trim($state['contact']['email']),
            'phone' => trim($state['contact']['phone']),
            'address_line' => trim($state['contact']['address_line']),
            'city' => trim($state['contact']['city']),
            'postal_code' => trim($state['contact']['postal_code']),
        ];

        $marketing = [
            'hero_title' => trim($state['marketing']['hero_title']),
            'hero_subtitle' => trim($state['marketing']['hero_subtitle']),
            'services_heading' => trim($state['marketing']['services_heading']),
            'services_subheading' => trim($state['marketing']['services_subheading']),
            'features_heading' => trim($state['marketing']['features_heading']),
            'features_subheading' => trim($state['marketing']['features_subheading']),
            'features' => [
                [
                    'title' => trim($state['marketing']['feature_one_title']),
                    'description' => trim($state['marketing']['feature_one_description']),
                ],
                [
                    'title' => trim($state['marketing']['feature_two_title']),
                    'description' => trim($state['marketing']['feature_two_description']),
                ],
                [
                    'title' => trim($state['marketing']['feature_three_title']),
                    'description' => trim($state['marketing']['feature_three_description']),
                ],
            ],
            'cta_heading' => trim($state['marketing']['cta_heading']),
            'cta_subheading' => trim($state['marketing']['cta_subheading']),
            'important_info_heading' => trim($state['marketing']['important_info_heading']),
            'important_info_points' => $this->splitImportantInfoPoints($state['marketing']['important_info_points'] ?? ''),
        ];

        app(SettingsManager::class)->updateGroups([
            'booking' => $booking,
            'map' => $map,
            'contact' => $contact,
            'marketing' => $marketing,
        ]);

        Notification::make()
            ->title('Zapisano ustawienia')
            ->success()
            ->send();
    }

    protected function splitImportantInfoPoints(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn (string $line) => $line !== ''));
    }
}
