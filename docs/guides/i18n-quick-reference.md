# i18n Quick Reference

Quick reference for using the internationalization system in the Paradocks application.

## Common Usage Patterns

### Blade Views

```blade
{{-- Format date according to user's locale --}}
{{ $appointment->scheduled_at->format(DateTimeFormat::date()) }}

{{-- Format datetime with locale awareness --}}
{{ $appointment->scheduled_at->format(DateTimeFormat::datetime()) }}

{{-- Force specific locale --}}
{{ $appointment->scheduled_at->format(DateTimeFormat::date('en')) }}

{{-- Database storage (always ISO) --}}
{{ $appointment->scheduled_at->format(DateTimeFormat::DATE_DB) }}
```

### Controllers

```php
use App\Support\DateTimeFormat;

// User's preferred format
$formatted = $date->format(DateTimeFormat::datetime());

// Specific locale
$formatted = $date->format(DateTimeFormat::datetime('en'));

// For database queries
$date->format(DateTimeFormat::DATE_DB);
```

### Filament Resources

```php
use App\Support\DateTimeFormat;

// TextColumn
TextColumn::make('scheduled_at')
    ->formatStateUsing(fn ($state) => $state->format(DateTimeFormat::datetime()))
    ->sortable(),

// DatePicker (still use Polish components for forms)
PolishDatePicker::make('scheduled_at')
    ->displayFormat(DateTimeFormat::date()) // Display according to locale
```

### Email Notifications

```php
use App\Support\DateTimeFormat;

// Use recipient's locale
$userLocale = $notifiable->locale;
$formatted = $date->format(DateTimeFormat::datetime($userLocale));
```

### API Responses

```php
// Always return ISO 8601 for APIs (let frontend format)
return [
    'scheduled_at' => $appointment->scheduled_at->format(DateTimeFormat::DATETIME_SYSTEM),
];
```

## Available Methods

### Locale-Aware (Use These!)

```php
DateTimeFormat::date()          // User's locale date format
DateTimeFormat::time()          // User's locale time format
DateTimeFormat::datetime()      // User's locale datetime format
DateTimeFormat::dateWithDay()   // Date with day abbreviation
DateTimeFormat::datetimeFull()  // Datetime with seconds
DateTimeFormat::firstDayOfWeek() // Week start day (0-6)
```

### System Formats (Always ISO)

```php
DateTimeFormat::DATE_SYSTEM     // 'Y-m-d'
DateTimeFormat::TIME_SYSTEM     // 'H:i:s'
DateTimeFormat::DATETIME_SYSTEM // 'Y-m-d H:i:s'
DateTimeFormat::DATE_DB         // 'Y-m-d' (MySQL DATE)
DateTimeFormat::TIME_DB         // 'H:i:s' (MySQL TIME)
DateTimeFormat::DATETIME_DB     // 'Y-m-d H:i:s' (MySQL DATETIME)
```

## User Model

```php
// Get user's locale
$user->locale;    // Returns 'pl' or 'en' (with fallback)

// Get user's timezone
$user->timezone;  // Returns 'Europe/Warsaw' (with fallback)

// Update user's locale
$user->update(['locale' => 'en']);

// Update user's timezone
$user->update(['timezone' => 'America/New_York']);
```

## Current Locale

```php
// Get current application locale
app()->getLocale();      // Returns 'pl' or 'en'

// Set locale temporarily
app()->setLocale('en');  // Changes for current request only

// Get available locales
config('app.available_locales');  // ['pl', 'en']

// Get locale display names
config('app.locale_names');  // ['pl' => 'Polski', 'en' => 'English']
```

## Format Examples

### Polish (pl)
```
Date:         20.11.2025
Time:         14:30
DateTime:     20.11.2025 14:30
With Day:     20.11.2025 (Śr)
Week Start:   Monday (1)
```

### English (en)
```
Date:         11/20/2025
Time:         2:30 PM
DateTime:     11/20/2025 2:30 PM
With Day:     11/20/2025 (Wed)
Week Start:   Sunday (0)
```

### System/Database (always)
```
Date:         2025-11-20
Time:         14:30:00
DateTime:     2025-11-20 14:30:00
```

## Language Switcher

### Route
```php
// routes/web.php
Route::post('/locale', [LocaleController::class, 'set'])->name('locale.set');
```

### Controller
```php
public function set(Request $request)
{
    $locale = $request->input('locale');

    if (in_array($locale, config('app.available_locales'))) {
        if ($user = $request->user()) {
            $user->update(['locale' => $locale]);
        }
        session(['locale' => $locale]);
    }

    return redirect()->back();
}
```

### Blade
```blade
<form action="{{ route('locale.set') }}" method="POST">
    @csrf
    <select name="locale" onchange="this.form.submit()">
        @foreach (config('app.locale_names') as $code => $name)
            <option value="{{ $code }}" @selected(app()->getLocale() === $code)>
                {{ $name }}
            </option>
        @endforeach
    </select>
</form>
```

## Adding New Locale

### 1. Edit config/formats.php
```php
'de' => [
    'date' => 'd.m.Y',
    'time' => 'H:i',
    'datetime' => 'd.m.Y H:i',
    'date_with_day' => 'd.m.Y (D)',
    'datetime_full' => 'd.m.Y H:i:s',
    'first_day_of_week' => 1,
],
```

### 2. Edit config/app.php
```php
'available_locales' => ['pl', 'en', 'de'],
'locale_names' => [
    'pl' => 'Polski',
    'en' => 'English',
    'de' => 'Deutsch',
],
```

### 3. Clear cache
```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
```

## Troubleshooting

### Locale not changing?
```bash
# 1. Clear config cache
docker compose exec app php artisan config:clear

# 2. Restart containers (clears OPcache)
docker compose restart app horizon queue scheduler

# 3. Check session is working
docker compose exec app php artisan tinker
>>> session()->all();
```

### Wrong format displayed?
```bash
# Check current locale
docker compose exec app php artisan tinker
>>> app()->getLocale();

# Check format config
>>> config('formats.pl.date');
>>> config('formats.en.date');
```

### Database issues?
```bash
# Verify migration
docker compose exec app php artisan migrate:status

# Check user table schema
docker compose exec mysql mysql -u paradocks -ppassword paradocks -e "DESCRIBE users;"
```

## Rules of Thumb

### Always Use:
✅ `DateTimeFormat::date()` for user-facing dates
✅ `DateTimeFormat::DATE_SYSTEM` for database storage
✅ Locale-aware methods (not constants)
✅ User's locale preference from `$user->locale`

### Never Use:
❌ Hardcoded format strings (`'d.m.Y'`)
❌ Constants for new code (`DateTimeFormat::DATE`)
❌ Polish format for all users
❌ User-facing formats in database

## See Also

- [Full i18n Guide](internationalization.md) - Complete documentation
- [Date/Time Formats](date-time-formats.md) - Original format guide
- [ADR-012](../decisions/ADR-012-datetime-format-standardization.md) - Format standardization decision
