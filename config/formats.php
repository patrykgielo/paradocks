<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Locale-Specific Date/Time Formats
    |--------------------------------------------------------------------------
    |
    | Define date and time formatting rules for each supported locale.
    | These formats are used throughout the application for user-facing content
    | (UI, emails, PDFs, reports) while system/database formats remain ISO standard.
    |
    | World-class i18n architecture (Stripe/Shopify level):
    | - User sees dates/times in their preferred format
    | - System stores everything in ISO 8601 (Y-m-d, H:i:s)
    | - Easy to add new locales: just add a new array key
    |
    | Usage:
    |   config('formats.pl.date')              // Returns 'd.m.Y'
    |   DateTimeFormat::date('en')             // Returns 'm/d/Y'
    |   $appointment->scheduled_at->format(DateTimeFormat::datetime())
    |
    | @see app/Support/DateTimeFormat.php - Locale-aware formatting methods
    | @see app/Http/Middleware/SetLocale.php - Automatic locale detection
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Polish (pl) - Default locale
    |--------------------------------------------------------------------------
    |
    | Format conventions:
    | - Date: European format (day.month.year)
    | - Time: 24-hour format
    | - Week starts: Monday
    |
    | Examples:
    | - Date: 20.11.2025
    | - Time: 14:30
    | - DateTime: 20.11.2025 14:30
    |
    */
    'pl' => [
        'date' => 'd.m.Y',
        'time' => 'H:i',
        'datetime' => 'd.m.Y H:i',
        'date_with_day' => 'd.m.Y (D)',      // 20.11.2025 (Śr)
        'datetime_full' => 'd.m.Y H:i:s',    // With seconds
        'first_day_of_week' => 1,             // Monday (ISO 8601)
    ],

    /*
    |--------------------------------------------------------------------------
    | English (en) - International standard
    |--------------------------------------------------------------------------
    |
    | Format conventions:
    | - Date: US format (month/day/year)
    | - Time: 12-hour format with AM/PM
    | - Week starts: Sunday (US convention)
    |
    | Examples:
    | - Date: 11/20/2025
    | - Time: 2:30 PM
    | - DateTime: 11/20/2025 2:30 PM
    |
    */
    'en' => [
        'date' => 'm/d/Y',
        'time' => 'h:i A',
        'datetime' => 'm/d/Y h:i A',
        'date_with_day' => 'm/d/Y (D)',      // 11/20/2025 (Wed)
        'datetime_full' => 'm/d/Y h:i:s A',  // With seconds
        'first_day_of_week' => 0,             // Sunday (US convention)
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Locale Examples (uncomment to enable)
    |--------------------------------------------------------------------------
    |
    | Copy-paste template for new locales:
    |
    | 'de' => [
    |     'date' => 'd.m.Y',                   // German format (same as Polish)
    |     'time' => 'H:i',                     // 24-hour
    |     'datetime' => 'd.m.Y H:i',
    |     'date_with_day' => 'd.m.Y (D)',
    |     'datetime_full' => 'd.m.Y H:i:s',
    |     'first_day_of_week' => 1,           // Monday
    | ],
    |
    | 'fr' => [
    |     'date' => 'd/m/Y',                   // French format (slash separator)
    |     'time' => 'H:i',                     // 24-hour
    |     'datetime' => 'd/m/Y H:i',
    |     'date_with_day' => 'd/m/Y (D)',
    |     'datetime_full' => 'd/m/Y H:i:s',
    |     'first_day_of_week' => 1,           // Monday
    | ],
    |
    | 'es' => [
    |     'date' => 'd/m/Y',                   // Spanish format
    |     'time' => 'H:i',                     // 24-hour
    |     'datetime' => 'd/m/Y H:i',
    |     'date_with_day' => 'd/m/Y (D)',
    |     'datetime_full' => 'd/m/Y H:i:s',
    |     'first_day_of_week' => 1,           // Monday
    | ],
    |
    | 'ja' => [
    |     'date' => 'Y年m月d日',               // Japanese format (kanji)
    |     'time' => 'H:i',                     // 24-hour
    |     'datetime' => 'Y年m月d日 H:i',
    |     'date_with_day' => 'Y年m月d日 (D)',
    |     'datetime_full' => 'Y年m月d日 H:i:s',
    |     'first_day_of_week' => 0,           // Sunday
    | ],
    |
    */

    /*
    |--------------------------------------------------------------------------
    | System/Technical Formats (Locale-Independent)
    |--------------------------------------------------------------------------
    |
    | These formats are NEVER changed and are used for:
    | - Database storage (MySQL DATE, TIME, DATETIME columns)
    | - API responses (ISO 8601 standard)
    | - Log files
    | - System integrations
    |
    | Always use ISO 8601 format (Y-m-d H:i:s) for technical data.
    | User-facing content is then formatted according to their locale.
    |
    | Access via DateTimeFormat class constants:
    |   DateTimeFormat::DATE_SYSTEM     // 'Y-m-d'
    |   DateTimeFormat::TIME_SYSTEM     // 'H:i:s'
    |   DateTimeFormat::DATETIME_SYSTEM // 'Y-m-d H:i:s'
    |
    */

];
