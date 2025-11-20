<?php

namespace App\Filament\Tables\Columns;

use App\Support\DateTimeFormat;
use Filament\Tables\Columns\TextColumn;

/**
 * Localized Date Column
 *
 * Automatically displays dates in user's locale format:
 * - Polish (pl): 20.11.2025 (d.m.Y)
 * - English (en): 11/20/2025 (m/d/Y)
 *
 * Usage:
 * ```php
 * LocalizedDateColumn::make('created_at')
 *     ->label('Created')
 *     ->sortable()
 * ```
 *
 * @see \App\Support\DateTimeFormat
 */
class LocalizedDateColumn extends TextColumn
{
    protected function setUp(): void
    {
        parent::setUp();

        $locale = app()->getLocale();

        $this->date(DateTimeFormat::date($locale));
    }
}
