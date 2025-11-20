<?php

namespace App\Support;

/**
 * Centralized Date/Time Format Constants
 *
 * Supports locale-aware formatting for internationalization.
 * Defaults to Polish format (d.m.Y) but adapts to app()->getLocale().
 *
 * @see docs/decisions/ADR-012-datetime-format-standardization.md
 * @see docs/guides/date-time-formats.md
 */
class DateTimeFormat
{
    // ===================================
    // USER-FACING FORMATS (Legacy - Polish)
    // ===================================

    /**
     * Date format: 20.11.2025
     * @deprecated Use date() method for locale-aware formatting
     */
    public const DATE = 'd.m.Y';

    /**
     * Time format: 14:30 (24-hour)
     * @deprecated Use time() method for locale-aware formatting
     */
    public const TIME = 'H:i';

    /**
     * DateTime format: 20.11.2025 14:30
     * @deprecated Use dateTime() method for locale-aware formatting
     */
    public const DATETIME = 'd.m.Y H:i';

    /**
     * Date with day name: 20.11.2025 (Śr)
     * Note: Day name will be in Polish if APP_LOCALE=pl
     * @deprecated Use dateWithDay() method for locale-aware formatting
     */
    public const DATE_WITH_DAY = 'd.m.Y (D)';

    /**
     * Full DateTime with seconds: 20.11.2025 14:30:45
     * @deprecated Use dateTimeFull() method for locale-aware formatting
     */
    public const DATETIME_FULL = 'd.m.Y H:i:s';

    // ===================================
    // SYSTEM/TECHNICAL FORMATS (ISO)
    // ===================================

    /**
     * System date format: 2025-11-20
     * Used for: Email/SMS logs, technical records
     */
    public const DATE_SYSTEM = 'Y-m-d';

    /**
     * System time format: 14:30:00
     */
    public const TIME_SYSTEM = 'H:i:s';

    /**
     * System DateTime format: 2025-11-20 14:30:00
     * Used for: Email/SMS event logs, audit trails
     */
    public const DATETIME_SYSTEM = 'Y-m-d H:i:s';

    // ===================================
    // DATABASE FORMATS
    // ===================================

    /**
     * Database date format: 2025-11-20
     * MySQL DATE column format
     */
    public const DATE_DB = 'Y-m-d';

    /**
     * Database time format: 14:30:00
     * MySQL TIME column format
     */
    public const TIME_DB = 'H:i:s';

    /**
     * Database DateTime format: 2025-11-20 14:30:00
     * MySQL DATETIME column format
     */
    public const DATETIME_DB = 'Y-m-d H:i:s';

    // ===================================
    // HELPER METHODS
    // ===================================

    /**
     * Get all user-facing formats
     *
     * @return array<string, string>
     */
    public static function getUserFormats(): array
    {
        return [
            'date' => self::DATE,
            'time' => self::TIME,
            'datetime' => self::DATETIME,
            'date_with_day' => self::DATE_WITH_DAY,
            'datetime_full' => self::DATETIME_FULL,
        ];
    }

    /**
     * Get all system/technical formats
     *
     * @return array<string, string>
     */
    public static function getSystemFormats(): array
    {
        return [
            'date' => self::DATE_SYSTEM,
            'time' => self::TIME_SYSTEM,
            'datetime' => self::DATETIME_SYSTEM,
        ];
    }

    /**
     * Get all database formats
     *
     * @return array<string, string>
     */
    public static function getDatabaseFormats(): array
    {
        return [
            'date' => self::DATE_DB,
            'time' => self::TIME_DB,
            'datetime' => self::DATETIME_DB,
        ];
    }

    // ===================================
    // LOCALE-AWARE METHODS (NEW)
    // ===================================
    //
    // These methods retrieve formats from config/formats.php based on the current
    // or specified locale. This enables world-class i18n without hardcoding formats.
    //
    // Adding a new locale is as simple as adding a new entry to config/formats.php.
    //

    /**
     * Get date format for specified locale.
     *
     * Retrieves format from config/formats.php for the given locale.
     * Falls back to Polish format if locale not found.
     *
     * Examples:
     * - pl: 20.11.2025 (d.m.Y)
     * - en: 11/20/2025 (m/d/Y)
     *
     * @param string|null $locale Locale code (defaults to app()->getLocale())
     * @return string PHP date format string
     */
    public static function date(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return config("formats.{$locale}.date", config('formats.pl.date', 'd.m.Y'));
    }

    /**
     * Get time format for specified locale.
     *
     * Retrieves format from config/formats.php for the given locale.
     * Falls back to 24-hour format if locale not found.
     *
     * Examples:
     * - pl: 14:30 (H:i)
     * - en: 2:30 PM (h:i A)
     *
     * @param string|null $locale Locale code (defaults to app()->getLocale())
     * @return string PHP time format string
     */
    public static function time(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return config("formats.{$locale}.time", config('formats.pl.time', 'H:i'));
    }

    /**
     * Get datetime format for specified locale.
     *
     * Retrieves format from config/formats.php for the given locale.
     * Falls back to Polish format if locale not found.
     *
     * Examples:
     * - pl: 20.11.2025 14:30 (d.m.Y H:i)
     * - en: 11/20/2025 2:30 PM (m/d/Y h:i A)
     *
     * @param string|null $locale Locale code (defaults to app()->getLocale())
     * @return string PHP datetime format string
     */
    public static function datetime(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return config("formats.{$locale}.datetime", config('formats.pl.datetime', 'd.m.Y H:i'));
    }

    /**
     * Get date format with day name for specified locale.
     *
     * Retrieves format from config/formats.php for the given locale.
     * Falls back to Polish format if locale not found.
     *
     * Examples:
     * - pl: 20.11.2025 (Śr) [d.m.Y (D)]
     * - en: 11/20/2025 (Wed) [m/d/Y (D)]
     *
     * Note: Day name abbreviation depends on app locale setting.
     *
     * @param string|null $locale Locale code (defaults to app()->getLocale())
     * @return string PHP date format string with day name
     */
    public static function dateWithDay(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return config("formats.{$locale}.date_with_day", config('formats.pl.date_with_day', 'd.m.Y (D)'));
    }

    /**
     * Get full datetime format with seconds for specified locale.
     *
     * Retrieves format from config/formats.php for the given locale.
     * Falls back to Polish format if locale not found.
     *
     * Examples:
     * - pl: 20.11.2025 14:30:45 (d.m.Y H:i:s)
     * - en: 11/20/2025 2:30:45 PM (m/d/Y h:i:s A)
     *
     * @param string|null $locale Locale code (defaults to app()->getLocale())
     * @return string PHP datetime format string with seconds
     */
    public static function datetimeFull(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return config("formats.{$locale}.datetime_full", config('formats.pl.datetime_full', 'd.m.Y H:i:s'));
    }

    /**
     * Get first day of week for specified locale.
     *
     * Retrieves first day from config/formats.php for the given locale.
     * Falls back to Monday (1) if locale not found.
     *
     * Returns:
     * - pl: 1 (Monday - ISO 8601 standard)
     * - en: 0 (Sunday - US convention)
     *
     * Used for:
     * - Calendar widgets (week start day)
     * - Date pickers
     * - Weekly schedule displays
     *
     * @param string|null $locale Locale code (defaults to app()->getLocale())
     * @return int Day number (0 = Sunday, 1 = Monday, ..., 6 = Saturday)
     */
    public static function firstDayOfWeek(?string $locale = null): int
    {
        $locale = $locale ?? app()->getLocale();
        return config("formats.{$locale}.first_day_of_week", config('formats.pl.first_day_of_week', 1));
    }
}
