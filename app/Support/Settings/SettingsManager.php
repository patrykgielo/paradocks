<?php

namespace App\Support\Settings;

use App\Models\Setting;
use Illuminate\Support\Arr;

class SettingsManager
{
    /**
     * Cached settings values keyed by group name.
     */
    protected array $cache = [];

    /**
     * Retrieve a full settings group merged with defaults.
     */
    public function getGroup(string $key, array $defaults = []): array
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $setting = Setting::query()->where('key', $key)->first();

        $values = $setting?->value ?? [];
        if (! is_array($values)) {
            $values = [];
        }

        return $this->cache[$key] = array_replace_recursive($defaults, $values);
    }

    /**
     * Persist a full group of settings.
     */
    public function setGroup(string $key, array $values): void
    {
        Setting::query()->updateOrCreate(
            ['key' => $key],
            [
                'group' => $key,
                'value' => $values,
            ]
        );

        $this->cache[$key] = $values;
    }

    /**
     * Update multiple groups in one call.
     */
    public function updateGroups(array $groups): void
    {
        foreach ($groups as $key => $values) {
            $this->setGroup($key, $values);
        }
    }

    public function bookingConfiguration(): array
    {
        return $this->getGroup('booking', $this->defaultBookingConfiguration());
    }

    public function bookingBusinessHours(): array
    {
        $config = $this->bookingConfiguration();

        return [
            'start' => Arr::get($config, 'business_hours_start', config('booking.business_hours.start')),
            'end' => Arr::get($config, 'business_hours_end', config('booking.business_hours.end')),
        ];
    }

    public function advanceBookingHours(): int
    {
        return (int) Arr::get(
            $this->bookingConfiguration(),
            'advance_booking_hours',
            config('booking.advance_booking_hours', 24)
        );
    }

    public function cancellationHours(): int
    {
        return (int) Arr::get(
            $this->bookingConfiguration(),
            'cancellation_hours',
            config('booking.cancellation_hours', 24)
        );
    }

    public function slotIntervalMinutes(): int
    {
        return (int) Arr::get(
            $this->bookingConfiguration(),
            'slot_interval_minutes',
            config('booking.slot_interval_minutes', 15)
        );
    }

    public function maxServiceDurationMinutes(): int
    {
        return (int) Arr::get(
            $this->bookingConfiguration(),
            'max_service_duration_minutes',
            config('booking.max_service_duration_minutes', 540)
        );
    }

    public function mapConfiguration(): array
    {
        return $this->getGroup('map', $this->defaultMapConfiguration());
    }

    public function contactInformation(): array
    {
        return $this->getGroup('contact', $this->defaultContactInformation());
    }

    public function marketingContent(): array
    {
        $defaults = $this->defaultMarketingContent();
        $marketing = $this->getGroup('marketing', $defaults);

        // Ensure features array is normalised with defaults to avoid missing indexes.
        $features = Arr::get($marketing, 'features', []);
        if (! is_array($features)) {
            $features = [];
        }

        $defaultFeatures = Arr::get($defaults, 'features', []);
        $marketing['features'] = array_values(array_replace($defaultFeatures, $features));

        // Normalise important info points.
        $points = Arr::get($marketing, 'important_info_points', []);
        if (! is_array($points)) {
            $points = [];
        }
        $marketing['important_info_points'] = array_values(array_filter(
            array_map('strval', $points),
            static fn (string $point) => $point !== ''
        ));

        return array_replace_recursive($defaults, $marketing);
    }

    protected function defaultBookingConfiguration(): array
    {
        return [
            'business_hours_start' => config('booking.business_hours.start', '09:00'),
            'business_hours_end' => config('booking.business_hours.end', '18:00'),
            'advance_booking_hours' => config('booking.advance_booking_hours', 24),
            'cancellation_hours' => config('booking.cancellation_hours', 24),
            'slot_interval_minutes' => config('booking.slot_interval_minutes', 15),
            'max_service_duration_minutes' => config('booking.max_service_duration_minutes', 540),
        ];
    }

    protected function defaultMapConfiguration(): array
    {
        return [
            'default_latitude' => 52.2297,
            'default_longitude' => 21.0122,
            'default_zoom' => 15,
            'country_code' => 'pl',
            'debug_panel_enabled' => true,
            'map_id' => config('services.google_maps.map_id'),
        ];
    }

    protected function defaultContactInformation(): array
    {
        return [
            'email' => 'kontakt@example.com',
            'phone' => '+48 123 456 789',
            'address_line' => 'ul. Przykładowa 1',
            'city' => 'Warszawa',
            'postal_code' => '00-000',
        ];
    }

    protected function defaultMarketingContent(): array
    {
        return [
            'hero_title' => 'Profesjonalne Czyszczenie i Detailing Samochodów',
            'hero_subtitle' => 'Zarezerwuj wizytę online w kilku prostych krokach. Gwarantujemy najwyższą jakość usług i satysfakcję klientów.',
            'services_heading' => 'Nasze Usługi Detailingowe',
            'services_subheading' => 'Wybierz pakiet dopasowany do potrzeb Twojego pojazdu. Wszystkie usługi wykonujemy z najwyższą starannością.',
            'features_heading' => 'Dlaczego Warto Nas Wybrać',
            'features_subheading' => 'Oferujemy najwyższy standard obsługi i jakości wykonanych usług',
            'features' => [
                [
                    'title' => 'Łatwa Rezerwacja Online',
                    'description' => 'Zarezerwuj wizytę w kilku kliknięciach, 24/7 dostępność systemu rezerwacji.',
                ],
                [
                    'title' => 'Natychmiastowe Potwierdzenie',
                    'description' => 'Otrzymasz potwierdzenie rezerwacji od razu po dokonaniu zapisu.',
                ],
                [
                    'title' => 'Elastyczne Godziny',
                    'description' => 'Wybierz termin dopasowany do Twojego harmonogramu.',
                ],
            ],
            'cta_heading' => 'Gotowy na Profesjonalny Detailing?',
            'cta_subheading' => 'Dołącz do setek zadowolonych klientów. Zarejestruj się i zarezerwuj swoją pierwszą wizytę już dziś.',
            'important_info_heading' => 'Ważne Informacje',
            'important_info_points' => [
                'Prosimy o przybycie 5 minut przed umówionym terminem.',
                'W przypadku spóźnienia powyżej 15 minut rezerwacja może zostać anulowana.',
                'Możesz anulować wizytę do :hours godzin przed terminem.',
            ],
        ];
    }
}
