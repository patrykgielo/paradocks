# Internationalization (i18n) Guide

## Overview

This application implements world-class internationalization (i18n) architecture inspired by industry leaders like Stripe and Shopify. The system supports multiple locales with automatic detection and user-specific preferences.

**Current Support:** Polish (pl), English (en)
**Easy Extension:** Add new locales by updating config files (no code changes required)

## Architecture Components

### 1. Database Layer
- **Migration:** `2025_11_20_231015_add_locale_to_users_table.php`
- **Fields Added:**
  - `locale` (VARCHAR 5, default 'pl') - User's preferred language
  - `timezone` (VARCHAR 50, default 'Europe/Warsaw') - User's timezone
- **Indexes:** Both fields indexed for performance

### 2. Configuration Files

#### config/formats.php
Defines locale-specific date/time formats and calendar conventions:
```php
'pl' => [
    'date' => 'd.m.Y',              // 20.11.2025
    'time' => 'H:i',                // 14:30
    'datetime' => 'd.m.Y H:i',      // 20.11.2025 14:30
    'date_with_day' => 'd.m.Y (D)', // 20.11.2025 (Śr)
    'first_day_of_week' => 1,       // Monday
],
```

#### config/app.php
Defines available locales and their display names:
```php
'available_locales' => ['pl', 'en'],
'locale_names' => [
    'pl' => 'Polski',
    'en' => 'English',
],
```

### 3. Middleware: SetLocale

**Location:** `app/Http/Middleware/SetLocale.php`
**Registration:** `bootstrap/app.php` (global middleware)

**Automatic Locale Detection Priority:**
1. Authenticated user's `locale` preference
2. Session value (from language switcher)
3. Browser `Accept-Language` header
4. Application default (`config('app.locale')`)

**What it does:**
- Sets `app()->setLocale()` for Laravel translations
- Sets `Carbon::setLocale()` for date/time formatting
- Persists guest user choice in session

### 4. DateTimeFormat Service

**Location:** `app/Support/DateTimeFormat.php`

**New Locale-Aware Methods:**
```php
// User-facing formats (adapt to locale)
DateTimeFormat::date()          // 'd.m.Y' for pl, 'm/d/Y' for en
DateTimeFormat::time()          // 'H:i' for pl, 'h:i A' for en
DateTimeFormat::datetime()      // Combined date + time
DateTimeFormat::dateWithDay()   // With day abbreviation
DateTimeFormat::datetimeFull()  // With seconds
DateTimeFormat::firstDayOfWeek() // 1 (Mon) for pl, 0 (Sun) for en

// System formats (immutable, always ISO 8601)
DateTimeFormat::DATE_SYSTEM     // Always 'Y-m-d'
DateTimeFormat::TIME_SYSTEM     // Always 'H:i:s'
DateTimeFormat::DATETIME_SYSTEM // Always 'Y-m-d H:i:s'
DateTimeFormat::DATE_DB         // Database storage format
```

**Backward Compatibility:**
```php
// Legacy constants still work (Polish format)
DateTimeFormat::DATE     // 'd.m.Y' (deprecated, use date() method)
DateTimeFormat::TIME     // 'H:i' (deprecated, use time() method)
DateTimeFormat::DATETIME // 'd.m.Y H:i' (deprecated, use datetime() method)
```

### 5. User Model Enhancements

**Location:** `app/Models/User.php`

**New Fillable Fields:**
```php
protected $fillable = [
    // ... existing fields
    'locale',
    'timezone',
];
```

**Accessor Methods:**
```php
$user->locale    // Returns user's locale or app default
$user->timezone  // Returns user's timezone or app default
```

## Usage Examples

### Formatting Dates in Blade Views

```blade
{{-- Automatic locale detection --}}
{{ $appointment->scheduled_at->format(DateTimeFormat::date()) }}
{{-- Output: 20.11.2025 (pl) or 11/20/2025 (en) --}}

{{-- Explicit locale --}}
{{ $appointment->scheduled_at->format(DateTimeFormat::datetime('en')) }}
{{-- Output: 11/20/2025 2:30 PM --}}

{{-- For database storage (always ISO) --}}
{{ $appointment->scheduled_at->format(DateTimeFormat::DATE_DB) }}
{{-- Output: 2025-11-20 (always) --}}
```

### Formatting Dates in Controllers

```php
use App\Support\DateTimeFormat;

class AppointmentController extends Controller
{
    public function show(Appointment $appointment)
    {
        // Automatically uses current user's locale
        $formatted = $appointment->scheduled_at->format(DateTimeFormat::datetime());

        // Explicit locale
        $englishFormat = $appointment->scheduled_at->format(DateTimeFormat::datetime('en'));

        return view('appointments.show', [
            'formatted_date' => $formatted,
        ]);
    }
}
```

### Filament Resources

```php
use App\Support\DateTimeFormat;

TextColumn::make('scheduled_at')
    ->label('Data wizyty')
    ->formatStateUsing(fn ($state) => $state->format(DateTimeFormat::datetime()))
    ->sortable(),
```

### Language Switcher Implementation

```php
// Controller method
public function setLocale(Request $request)
{
    $locale = $request->input('locale');

    if (in_array($locale, config('app.available_locales'))) {
        // For authenticated users: update database
        if ($user = $request->user()) {
            $user->update(['locale' => $locale]);
        }

        // For guests: store in session
        session(['locale' => $locale]);

        // Middleware will pick it up on next request
    }

    return redirect()->back();
}
```

```blade
{{-- Blade component --}}
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

### Email Notifications

```php
use App\Support\DateTimeFormat;

class AppointmentConfirmation extends Notification
{
    public function toMail($notifiable)
    {
        // Use recipient's locale preference
        $userLocale = $notifiable->locale;
        $formattedDate = $this->appointment->scheduled_at
            ->format(DateTimeFormat::datetime($userLocale));

        return (new MailMessage)
            ->subject('Appointment Confirmation')
            ->line("Your appointment is scheduled for: {$formattedDate}");
    }
}
```

### API Responses

```php
// Always use ISO 8601 for API responses (machine-readable)
return response()->json([
    'appointment' => [
        'id' => $appointment->id,
        'scheduled_at' => $appointment->scheduled_at->format(DateTimeFormat::DATETIME_SYSTEM),
        // Frontend can format according to user's locale
    ],
]);
```

## Adding a New Locale

**Example: Adding German (de)**

### Step 1: Add Format Configuration

Edit `config/formats.php`:
```php
'de' => [
    'date' => 'd.m.Y',              // German format (same as Polish)
    'time' => 'H:i',                // 24-hour
    'datetime' => 'd.m.Y H:i',
    'date_with_day' => 'd.m.Y (D)',
    'datetime_full' => 'd.m.Y H:i:s',
    'first_day_of_week' => 1,       // Monday
],
```

### Step 2: Add to Available Locales

Edit `config/app.php`:
```php
'available_locales' => ['pl', 'en', 'de'],
'locale_names' => [
    'pl' => 'Polski',
    'en' => 'English',
    'de' => 'Deutsch',
],
```

### Step 3: Add Translation Files (Optional)

Create `resources/lang/de.json` for Laravel translations:
```json
{
    "Welcome": "Willkommen",
    "Login": "Anmelden"
}
```

### Step 4: Clear and Recache Config

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan config:cache
```

**That's it!** No code changes required. The system automatically supports the new locale.

## Best Practices

### DO:
✅ Use `DateTimeFormat::date()` for locale-aware formatting
✅ Use `DateTimeFormat::DATE_SYSTEM` for database storage
✅ Use `DateTimeFormat::DATE_DB` for MySQL DATE columns
✅ Store all dates in database as ISO 8601 (Y-m-d H:i:s)
✅ Format dates for display at the presentation layer (Blade, API responses)
✅ Respect user's locale preference from database
✅ Provide language switcher in UI

### DON'T:
❌ Don't hardcode date formats in views (`format('d.m.Y')`)
❌ Don't store formatted dates in database
❌ Don't mix user-facing formats with system formats
❌ Don't forget to handle timezone conversions for users in different zones
❌ Don't assume all users want Polish format

## Testing the System

### Verify Migration
```bash
docker compose exec app php artisan migrate:status
# Should show: 2025_11_20_231015_add_locale_to_users_table [Ran]
```

### Test Locale Detection
```bash
docker compose exec app php artisan tinker
>>> app()->setLocale('en');
>>> DateTimeFormat::date();
# Output: "m/d/Y"

>>> app()->setLocale('pl');
>>> DateTimeFormat::date();
# Output: "d.m.Y"
```

### Test User Locale
```bash
docker compose exec app php artisan tinker
>>> $user = User::first();
>>> $user->locale = 'en';
>>> $user->save();
>>> $user->locale;
# Output: "en"
```

## Performance Considerations

### Indexes
Both `locale` and `timezone` fields are indexed for fast filtering:
```sql
-- Efficient query for bulk emails by locale
SELECT * FROM users WHERE locale = 'pl' AND email_verified_at IS NOT NULL;

-- Efficient query for timezone-based scheduling
SELECT * FROM users WHERE timezone = 'Europe/Warsaw';
```

### Config Caching
In production, always cache config files:
```bash
php artisan config:cache
```

This ensures config access is instant (no file I/O).

## Migration Path

### Existing Codebase
Old code using constants continues to work:
```php
// Still works (backward compatible)
DateTimeFormat::DATE     // Returns 'd.m.Y'
DateTimeFormat::TIME     // Returns 'H:i'
DateTimeFormat::DATETIME // Returns 'd.m.Y H:i'
```

### Gradual Migration
Update code incrementally to use new methods:
```php
// Old (still works)
$date->format(DateTimeFormat::DATE);

// New (locale-aware)
$date->format(DateTimeFormat::date());
```

### Breaking Changes
**None!** This implementation is 100% backward compatible.

## Troubleshooting

### Locale not changing
1. Clear config cache: `php artisan config:clear`
2. Check middleware is registered in `bootstrap/app.php`
3. Verify session is working (guest users)
4. Check user's `locale` field in database

### Wrong date format displayed
1. Verify `config/formats.php` has correct format for locale
2. Check `app()->getLocale()` returns expected locale
3. Ensure you're using `DateTimeFormat::date()` not `DateTimeFormat::DATE`

### OPcache issues (code changes not applying)
```bash
# Restart containers to clear PHP-FPM OPcache
docker compose restart app horizon queue scheduler
```

## Related Documentation

- [Date/Time Formats Guide](date-time-formats.md) - Original format standardization
- [ADR-012: DateTime Format Standardization](../decisions/ADR-012-datetime-format-standardization.md)
- [User Model Documentation](../architecture/user-model.md)

## Future Enhancements

Potential improvements for future iterations:

1. **Automatic Translation Files:** Generate translation files for Filament admin panel
2. **Timezone Conversion:** Helper methods for converting between user timezone and system timezone
3. **Locale Switcher Widget:** Filament widget for easy locale switching in admin panel
4. **Locale-Aware Validation:** Custom validation rules that respect user's locale
5. **Number Formatting:** Extend system to handle number formats (1,234.56 vs 1.234,56)
6. **Currency Formatting:** Add multi-currency support with locale-aware formatting
7. **Right-to-Left (RTL) Support:** Add support for RTL languages (Arabic, Hebrew)

## Summary

This i18n implementation provides:
- ✅ Professional, scalable architecture
- ✅ Automatic locale detection (user → session → browser → default)
- ✅ User-specific locale preferences stored in database
- ✅ Backward compatible with existing code
- ✅ Easy to extend with new locales (config-driven)
- ✅ Separation of user-facing vs system formats
- ✅ Performance optimized (indexed fields, config caching)
- ✅ Zero breaking changes

You now have a foundation that can scale from 2 locales to 50+ locales without architectural changes.
