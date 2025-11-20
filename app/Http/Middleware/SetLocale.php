<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set application locale based on user preference, session, or browser settings.
 *
 * Priority order (highest to lowest):
 * 1. User preference (authenticated user's locale field)
 * 2. Session value (explicitly set via language switcher)
 * 3. Browser Accept-Language header
 * 4. Application default (config('app.locale'))
 *
 * Sets both Laravel app locale and Carbon locale for consistent date/time formatting.
 *
 * World-class i18n architecture (Stripe/Shopify level):
 * - Automatic locale detection from multiple sources
 * - User-specific overrides persist across sessions
 * - Graceful fallback to sensible defaults
 * - Easy to extend with additional detection methods
 *
 * Usage:
 * - Automatically applied via bootstrap/app.php middleware registration
 * - No manual intervention required in controllers/routes
 * - Access current locale: app()->getLocale() or App::getLocale()
 *
 * @see config/app.php - Available locales and locale names
 * @see config/formats.php - Locale-specific date/time formats
 * @see database/migrations/*_add_locale_to_users_table.php - User locale storage
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        // Set Laravel application locale (affects translations, validation messages, etc.)
        App::setLocale($locale);

        // Set Carbon locale for date/time formatting (diffForHumans, localized month names, etc.)
        // This ensures Carbon::now()->diffForHumans() returns "2 godziny temu" (pl) or "2 hours ago" (en)
        Carbon::setLocale($locale);

        // Store locale in session for persistence across requests (if not from user preference)
        // This allows language switchers to maintain state even for guest users
        if (!$request->user() && !Session::has('locale')) {
            Session::put('locale', $locale);
        }

        return $next($request);
    }

    /**
     * Determine the best locale for the current request.
     *
     * Priority order:
     * 1. Authenticated user's locale preference
     * 2. Session locale (set via language switcher)
     * 3. Browser Accept-Language header
     * 4. Application default
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string Two-letter locale code (pl, en, etc.)
     */
    protected function determineLocale(Request $request): string
    {
        $availableLocales = config('app.available_locales', ['pl', 'en']);
        $defaultLocale = config('app.locale', 'pl');

        // Priority 1: Authenticated user's preference
        if ($request->user() && $request->user()->locale) {
            $userLocale = $request->user()->locale;

            // Validate user locale is supported
            if (in_array($userLocale, $availableLocales)) {
                return $userLocale;
            }
        }

        // Priority 2: Session value (language switcher)
        if (Session::has('locale')) {
            $sessionLocale = Session::get('locale');

            // Validate session locale is supported
            if (in_array($sessionLocale, $availableLocales)) {
                return $sessionLocale;
            }
        }

        // Priority 3: Browser Accept-Language header
        $browserLocale = $this->detectBrowserLocale($request, $availableLocales);
        if ($browserLocale) {
            return $browserLocale;
        }

        // Priority 4: Application default
        return $defaultLocale;
    }

    /**
     * Detect locale from browser Accept-Language header.
     *
     * Parses the Accept-Language header and finds the best match from available locales.
     * Handles both simple locales (en) and regional variants (en-US, pt-BR).
     *
     * Examples:
     * - "pl,en-US;q=0.9,en;q=0.8" → returns 'pl'
     * - "en-US,en;q=0.9" → returns 'en'
     * - "fr-FR,fr;q=0.9" → returns null (if 'fr' not in available_locales)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $availableLocales  List of supported locale codes
     * @return string|null Matched locale code or null if no match
     */
    protected function detectBrowserLocale(Request $request, array $availableLocales): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header: "pl,en-US;q=0.9,en;q=0.8"
        // Result: ['pl' => 1.0, 'en-US' => 0.9, 'en' => 0.8]
        $languages = $this->parseAcceptLanguage($acceptLanguage);

        // Sort by quality value (highest first)
        arsort($languages);

        // Find first match in available locales
        foreach (array_keys($languages) as $lang) {
            // Try exact match first (e.g., 'pl' or 'en')
            if (in_array($lang, $availableLocales)) {
                return $lang;
            }

            // Try base locale for regional variants (e.g., 'en-US' → 'en')
            $baseLang = substr($lang, 0, 2);
            if (in_array($baseLang, $availableLocales)) {
                return $baseLang;
            }
        }

        return null;
    }

    /**
     * Parse Accept-Language header into locale => quality pairs.
     *
     * @param  string  $header  Accept-Language header value
     * @return array  Locale => quality mapping (e.g., ['pl' => 1.0, 'en' => 0.9])
     */
    protected function parseAcceptLanguage(string $header): array
    {
        $languages = [];

        // Split by comma: "pl,en-US;q=0.9" → ["pl", "en-US;q=0.9"]
        $parts = explode(',', $header);

        foreach ($parts as $part) {
            $part = trim($part);

            // Split by semicolon: "en-US;q=0.9" → ["en-US", "q=0.9"]
            $subParts = explode(';', $part);

            $locale = trim($subParts[0]);
            $quality = 1.0; // Default quality if not specified

            // Extract quality value if present
            if (isset($subParts[1]) && str_starts_with(trim($subParts[1]), 'q=')) {
                $quality = (float) substr(trim($subParts[1]), 2);
            }

            $languages[$locale] = $quality;
        }

        return $languages;
    }
}
