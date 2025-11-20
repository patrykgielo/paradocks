<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add internationalization support to users table.
 *
 * Adds locale preference and timezone settings for personalized date/time formatting
 * and content localization. Supports world-class i18n architecture (Stripe/Shopify level).
 *
 * Fields:
 * - locale: User's preferred language (pl, en, etc.) - 5 chars to support locales like en-US, pt-BR
 * - timezone: User's timezone for accurate datetime display (e.g., Europe/Warsaw, America/New_York)
 *
 * Both fields have sensible defaults for Polish users but can be changed per-user.
 *
 * @see config/formats.php - Locale-specific date/time formatting rules
 * @see app/Http/Middleware/SetLocale.php - Automatic locale detection and setting
 * @see app/Support/DateTimeFormat.php - Locale-aware formatting methods
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Locale preference: pl, en, de, fr, etc. (max 5 chars for locales like en-US)
            $table->string('locale', 5)->default('pl')->after('preferred_language');

            // Timezone for accurate datetime display (IANA timezone database names)
            // Examples: Europe/Warsaw, America/New_York, Asia/Tokyo, UTC
            $table->string('timezone', 50)->default('Europe/Warsaw')->after('locale');

            // Index for performance when filtering users by locale (e.g., for bulk emails)
            $table->index('locale');

            // Index for performance when filtering by timezone (e.g., for scheduled tasks)
            $table->index('timezone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['locale']);
            $table->dropIndex(['timezone']);
            $table->dropColumn(['locale', 'timezone']);
        });
    }
};
