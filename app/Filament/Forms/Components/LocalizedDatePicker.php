<?php

namespace App\Filament\Forms\Components;

use App\Support\DateTimeFormat;
use Filament\Forms\Components\DatePicker;

/**
 * Localized Date Picker
 *
 * Automatically adapts to user's locale (app()->getLocale()):
 * - Polish (pl): 20.11.2025 (d.m.Y), Monday first day
 * - English (en): 11/20/2025 (m/d/Y), Sunday first day
 * - Database format: Always 2025-11-20 (automatic conversion)
 *
 * Usage:
 * ```php
 * LocalizedDatePicker::make('date')
 *     ->label('Appointment Date')
 *     ->required()
 * ```
 *
 * @see \App\Support\DateTimeFormat
 */
class LocalizedDatePicker extends DatePicker
{
    protected function setUp(): void
    {
        parent::setUp();

        $locale = app()->getLocale();

        $this->native(false)
             ->displayFormat(DateTimeFormat::date($locale))
             ->format(DateTimeFormat::DATE_DB)
             ->locale($locale)
             ->firstDayOfWeek(DateTimeFormat::firstDayOfWeek($locale));
    }
}
