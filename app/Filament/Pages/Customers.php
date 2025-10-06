<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Customers extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.customers';
    protected static ?string $navigationGroup = 'Customer Management';
}
