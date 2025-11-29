<?php

declare(strict_types=1);

namespace App\Support\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * SettingsManager Service
 *
 * Manages application settings with caching and dot notation support.
 * Singleton service registered in AppServiceProvider.
 */
class SettingsManager
{
    /**
     * Cache duration in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Cache key prefix.
     */
    private const CACHE_PREFIX = 'settings';

    /**
     * Get a setting value by dot notation path.
     *
     * Example: get('booking.business_hours_start', '09:00')
     *
     * @param  string  $path  Dot notation path (group.key)
     * @param  mixed  $default  Default value if setting not found
     */
    public function get(string $path, mixed $default = null): mixed
    {
        [$group, $key] = $this->parsePath($path);

        $cacheKey = $this->getCacheKey($group, $key);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group, $key, $default) {
            $setting = Setting::group($group)->key($key)->first();

            if (! $setting) {
                return $default;
            }

            // If value is an array with single element, return the element
            // Otherwise return the full array/value
            $value = $setting->value;

            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                return $value[0];
            }

            return $value;
        });
    }

    /**
     * Set a setting value by dot notation path.
     *
     * Example: set('booking.business_hours_start', '10:00')
     *
     * @param  string  $path  Dot notation path (group.key)
     * @param  mixed  $value  Value to store
     */
    public function set(string $path, mixed $value): bool
    {
        [$group, $key] = $this->parsePath($path);

        // Wrap scalar values in array for JSON storage
        $jsonValue = is_array($value) ? $value : [$value];

        Setting::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $jsonValue]
        );

        // Clear cache for this setting
        $this->clearCache($group, $key);

        return true;
    }

    /**
     * Bulk update multiple groups of settings.
     *
     * Example: updateGroups(['booking' => ['business_hours_start' => '10:00'], 'email' => [...]])
     *
     * @param  array<string, array<string, mixed>>  $groups  Associative array of groups and their key-value pairs
     */
    public function updateGroups(array $groups): bool
    {
        foreach ($groups as $group => $settings) {
            foreach ($settings as $key => $value) {
                $this->set("{$group}.{$key}", $value);
            }
        }

        return true;
    }

    /**
     * Get all settings grouped by group.
     *
     * Returns: ['booking' => ['business_hours_start' => '09:00', ...], 'email' => [...]]
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $settings = Setting::all();

        $grouped = [];

        foreach ($settings as $setting) {
            $value = $setting->value;

            // Unwrap single-element arrays
            if (is_array($value) && count($value) === 1 && isset($value[0])) {
                $value = $value[0];
            }

            $grouped[$setting->group][$setting->key] = $value;
        }

        return $grouped;
    }

    /**
     * Get all settings for a specific group.
     *
     * Example: group('booking') returns ['business_hours_start' => '09:00', ...]
     *
     * @param  string  $group  Group name
     * @return array<string, mixed>
     */
    public function group(string $group): array
    {
        $cacheKey = $this->getCacheKey($group);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($group) {
            $settings = Setting::group($group)->get();

            $result = [];

            foreach ($settings as $setting) {
                $value = $setting->value;

                // Unwrap single-element arrays
                if (is_array($value) && count($value) === 1 && isset($value[0])) {
                    $value = $value[0];
                }

                $result[$setting->key] = $value;
            }

            return $result;
        });
    }

    /**
     * Parse dot notation path into group and key.
     *
     * @param  string  $path  Dot notation path (e.g., 'booking.business_hours_start')
     * @return array{0: string, 1: string} [group, key]
     *
     * @throws \InvalidArgumentException
     */
    private function parsePath(string $path): array
    {
        $parts = explode('.', $path, 2);

        if (count($parts) !== 2) {
            throw new \InvalidArgumentException(
                "Invalid setting path: {$path}. Expected format: 'group.key'"
            );
        }

        return $parts;
    }

    /**
     * Generate cache key for a setting.
     *
     * @param  string  $group  Group name
     * @param  string|null  $key  Setting key (optional)
     */
    private function getCacheKey(string $group, ?string $key = null): string
    {
        if ($key === null) {
            return self::CACHE_PREFIX.":{$group}";
        }

        return self::CACHE_PREFIX.":{$group}:{$key}";
    }

    /**
     * Clear cache for a specific setting or entire group.
     *
     * @param  string  $group  Group name
     * @param  string|null  $key  Setting key (optional, clears entire group if null)
     */
    private function clearCache(string $group, ?string $key = null): void
    {
        if ($key === null) {
            // Clear entire group cache
            Cache::forget($this->getCacheKey($group));
        } else {
            // Clear specific setting cache
            Cache::forget($this->getCacheKey($group, $key));
            // Also clear group cache as it contains this setting
            Cache::forget($this->getCacheKey($group));
        }
    }

    // ========================================================================
    // Helper Methods for Common Setting Groups
    // ========================================================================

    /**
     * Get all booking configuration settings.
     *
     * @return array<string, mixed>
     */
    public function bookingConfiguration(): array
    {
        return $this->group('booking');
    }

    /**
     * Get booking business hours.
     *
     * @return array{start: string, end: string}
     */
    public function bookingBusinessHours(): array
    {
        return [
            'start' => $this->get('booking.business_hours_start', '09:00'),
            'end' => $this->get('booking.business_hours_end', '18:00'),
        ];
    }

    /**
     * Get advance booking hours requirement.
     */
    public function advanceBookingHours(): int
    {
        return (int) $this->get('booking.advance_booking_hours', 24);
    }

    /**
     * Get cancellation policy hours.
     */
    public function cancellationHours(): int
    {
        return (int) $this->get('booking.cancellation_hours', 24);
    }

    /**
     * Get time slot interval in minutes.
     */
    public function slotIntervalMinutes(): int
    {
        return (int) $this->get('booking.slot_interval_minutes', 30);
    }

    /**
     * Get all map configuration settings.
     *
     * @return array<string, mixed>
     */
    public function mapConfiguration(): array
    {
        return $this->group('map');
    }

    /**
     * Get all contact information settings.
     *
     * @return array<string, mixed>
     */
    public function contactInformation(): array
    {
        return $this->group('contact');
    }

    /**
     * Get all marketing content settings.
     *
     * @return array<string, mixed>
     */
    public function marketingContent(): array
    {
        return $this->group('marketing');
    }
}
