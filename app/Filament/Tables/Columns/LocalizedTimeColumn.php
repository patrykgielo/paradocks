<?php

namespace App\Filament\Tables\Columns;

use App\Support\DateTimeFormat;
use Filament\Tables\Columns\TextColumn;

/**
 * Localized Time Column
 *
 * Automatically displays time in user's locale format:
 * - All locales: 14:30 (24-hour format, H:i)
 *
 * Usage:
 * ```php
 * LocalizedTimeColumn::make('start_time')
 *     ->label('Start Time')
 *     ->sortable()
 * ```
 *
 * @see \App\Support\DateTimeFormat
 */
class LocalizedTimeColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $locale = app()->getLocale();

        $this->time(DateTimeFormat::time($locale));
    }
}
