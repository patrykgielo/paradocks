<?php

namespace App\Filament\Tables\Columns;

use App\Support\DateTimeFormat;
use Filament\Tables\Columns\TextColumn;

/**
 * Localized DateTime Column
 *
 * Automatically displays datetime in user's locale format:
 * - Polish (pl): 20.11.2025 14:30 (d.m.Y H:i)
 * - English (en): 11/20/2025 14:30 (m/d/Y H:i)
 *
 * Usage:
 * ```php
 * LocalizedDateTimeColumn::make('created_at')
 *     ->label('Created At')
 *     ->sortable()
 * ```
 *
 * @see \App\Support\DateTimeFormat
 */
class LocalizedDateTimeColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $locale = app()->getLocale();

        $this->dateTime(DateTimeFormat::dateTime($locale));
    }
}
