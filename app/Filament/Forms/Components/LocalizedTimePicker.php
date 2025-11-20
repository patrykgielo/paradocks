<?php

namespace App\Filament\Forms\Components;

use App\Support\DateTimeFormat;
use Filament\Forms\Components\TimePicker;

/**
 * Localized Time Picker
 *
 * Automatically adapts to user's locale (app()->getLocale()):
 * - All locales: 14:30 (24-hour format, H:i)
 * - No seconds display
 *
 * Usage:
 * ```php
 * LocalizedTimePicker::make('start_time')
 *     ->label('Start Time')
 *     ->required()
 * ```
 *
 * @see \App\Support\DateTimeFormat
 */
class LocalizedTimePicker extends TimePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $locale = app()->getLocale();

        $this->seconds(false)
             ->format(DateTimeFormat::time($locale))
             ->locale($locale);
    }
}
