<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Users extends Page
{
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 10;


    protected static string $view = 'filament.pages.users';
}
