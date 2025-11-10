# Settings System

Centralized configuration management via Filament admin panel.

## Overview

- **SettingsManager** singleton service
- Settings stored in database (`settings` table)
- 4 setting groups: booking, map, contact, marketing
- Filament page: `/admin/system-settings`

## Setting Groups

**1. Booking** (`booking` group):
```php
business_hours_start, business_hours_end, slot_interval_minutes,
advance_booking_hours, cancellation_hours, max_service_duration_minutes
```

**2. Map** (`map` group):
```php
default_latitude, default_longitude, default_zoom, country_code,
map_id, debug_panel_enabled
```

**3. Contact** (`contact` group):
```php
email, phone, address_line, city, postal_code
```

**4. Marketing** (`marketing` group):
```php
hero_title, hero_subtitle, services_heading, features_heading, cta_heading, ...
```

## Usage

```php
// Via dependency injection
public function __construct(private SettingsManager $settings) {}
$config = $this->settings->bookingConfiguration();

// Via app() helper
$settings = app(SettingsManager::class);
$email = $settings->get('contact.email', 'default@example.com');
```

## Helper Methods

```php
$settings->bookingConfiguration()    → array (all booking settings)
$settings->bookingBusinessHours()    → ['start' => '09:00', 'end' => '18:00']
$settings->advanceBookingHours()     → int
$settings->mapConfiguration()        → array
$settings->contactInformation()      → array
$settings->marketingContent()        → array
```

## Important: Livewire Constructor Injection

**❌ WRONG:**
```php
class SystemSettings extends Page
{
    public function __construct(protected SettingsManager $settings) {} // Error!
}
```

**✅ CORRECT:**
```php
class SystemSettings extends Page
{
    public function mount(): void {
        $settings = app(SettingsManager::class); // Use app() helper
        $this->form->fill($settings->all());
    }
}
```

**Why?** Livewire instantiates components via `new ComponentClass()` without arguments.

## See Also

- CLAUDE.md (lines 346-547) - Full documentation
