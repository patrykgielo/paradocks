# Filament v4 Best Practices

**Version:** Filament v4.2.3
**Date:** December 16, 2025

This guide provides best practices and patterns for working with Filament v4 in the Paradocks application.

## Component Namespace Reference

### Critical Rule: Layout vs Data Components

Filament v4 split components into two namespaces based on their purpose:

| Component Type | Namespace | Import Example |
|----------------|-----------|----------------|
| **Layout Components** | `Filament\Schemas\Components\*` | `use Filament\Schemas\Components\Section;` |
| **Data Entry Components** | `Filament\Infolists\Components\*` | `use Filament\Infolists\Components\TextEntry;` |
| **Schema Definition** | `Filament\Schemas\Schema` | `use Filament\Schemas\Schema;` |

### Layout Components (Schemas Namespace)

Always import from `Filament\Schemas\Components\*`:

```php
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\View;
use Filament\Schemas\Components\Livewire;
```

### Data Entry Components (Infolists Namespace)

Always import from `Filament\Infolists\Components\*`:

```php
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\KeyValueEntry;
```

### Schema Type

```php
use Filament\Schemas\Schema;

public function infolist(Schema $schema): Schema
{
    return $schema->components([
        // ...
    ]);
}
```

## Complete ViewRecord Example

```php
<?php

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Filament\Resources\AppointmentResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewAppointment extends ViewRecord
{
    protected static string $resource = AppointmentResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('service.name')
                            ->label('Service'),
                        TextEntry::make('customer.name')
                            ->label('Customer'),
                        IconEntry::make('status')
                            ->boolean(),
                    ])
                    ->columns(2),

                Section::make('Location')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('location_latitude')
                                    ->label('Latitude'),
                                TextEntry::make('location_longitude')
                                    ->label('Longitude'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
```

## Complete Resource Example

```php
<?php

namespace App\Filament\Resources;

use App\Models\EmailSend;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmailSendResource extends Resource
{
    protected static ?string $model = EmailSend::class;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Email Details')
                    ->schema([
                        TextEntry::make('recipient_email')
                            ->label('Recipient')
                            ->copyable(),

                        TextEntry::make('subject')
                            ->label('Subject'),
                    ])
                    ->columns(2),

                Section::make('Content')
                    ->schema([
                        ViewEntry::make('body_html')
                            ->view('filament.resources.email-send.html-preview')
                            ->columnSpanFull(),
                    ]),

                Section::make('Events')
                    ->schema([
                        RepeatableEntry::make('emailEvents')
                            ->schema([
                                TextEntry::make('event_type')
                                    ->badge(),
                                TextEntry::make('occurred_at')
                                    ->dateTime(),
                            ]),
                    ])
                    ->collapsed(),
            ]);
    }
}
```

## Common Mistakes to Avoid

### Mistake 1: Using Infolists Namespace for Layout Components

```php
// ❌ WRONG - Will cause "Class not found" error
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

Section::make('Details')  // Class "Filament\Infolists\Components\Section" not found
```

```php
// ✅ CORRECT
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

Section::make('Details')  // Works!
```

### Mistake 2: Using Namespace Aliases

```php
// ❌ AVOID - Hard to debug, unclear which components are layout vs data
use Filament\Infolists;

Infolists\Components\Section::make('Details')  // Will fail silently
```

```php
// ✅ CORRECT - Explicit imports
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

Section::make('Details')
    ->schema([
        TextEntry::make('field'),
    ])
```

### Mistake 3: Mixing Forms and Infolists Namespaces

```php
// ❌ WRONG - Don't mix form and infolist components
use Filament\Forms\Components\Section;  // Forms namespace
use Filament\Infolists\Components\TextEntry;

public function infolist(Schema $schema): Schema
{
    return $schema->components([
        Section::make('Details')  // Wrong component type!
    ]);
}
```

```php
// ✅ CORRECT - Use Schemas namespace for infolists
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

public function infolist(Schema $schema): Schema
{
    return $schema->components([
        Section::make('Details')
            ->schema([
                TextEntry::make('field'),
            ]),
    ]);
}
```

## Verification Commands

### Check Namespace Correctness

```bash
# Find incorrect Section imports
grep -r "use Filament\\\\Infolists\\\\Components\\\\Section" app/Filament --include="*.php"

# Find incorrect Grid imports
grep -r "use Filament\\\\Infolists\\\\Components\\\\Grid" app/Filament --include="*.php"

# Find incorrect Fieldset imports
grep -r "use Filament\\\\Infolists\\\\Components\\\\Fieldset" app/Filament --include="*.php"
```

If these commands return results, fix them immediately!

### Test Class Loading

```bash
docker compose exec app php -r "
echo 'Layout components (Schemas):' . PHP_EOL;
echo 'Section: ' . (class_exists('Filament\Schemas\Components\Section') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'Grid: ' . (class_exists('Filament\Schemas\Components\Grid') ? 'OK' : 'FAIL') . PHP_EOL;
echo PHP_EOL . 'Data components (Infolists):' . PHP_EOL;
echo 'TextEntry: ' . (class_exists('Filament\Infolists\Components\TextEntry') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'IconEntry: ' . (class_exists('Filament\Infolists\Components\IconEntry') ? 'OK' : 'FAIL') . PHP_EOL;
"
```

Expected output:
```
Layout components (Schemas):
Section: OK
Grid: OK

Data components (Infolists):
TextEntry: OK
IconEntry: OK
```

### Syntax Check

```bash
# Check all Filament resource files
find app/Filament -name "*.php" -exec php -l {} \; | grep -v "No syntax errors"
```

No output = all files are syntactically correct.

## IDE Autocomplete Setup

### PhpStorm / VSCode with Intelephense

If your IDE shows incorrect autocomplete suggestions, regenerate IDE helper files:

```bash
docker compose exec app composer require --dev barryvdh/laravel-ide-helper
docker compose exec app php artisan ide-helper:generate
docker compose exec app php artisan ide-helper:models
docker compose exec app php artisan ide-helper:meta
```

## Migration from Filament v3

If upgrading from Filament v3, use Rector to automate namespace changes:

```bash
# Install Rector
composer require --dev rector/rector

# Run Filament upgrade rector
vendor/bin/rector process app/Filament --config=vendor/filament/upgrade/rector.php --dry-run

# Apply changes
vendor/bin/rector process app/Filament --config=vendor/filament/upgrade/rector.php
```

**Manual Review Required:** Always review Rector changes before committing!

## Quick Reference Card

Print this and keep it visible:

```
╔══════════════════════════════════════════════════════════════╗
║           FILAMENT v4 INFOLIST COMPONENT NAMESPACES          ║
╠══════════════════════════════════════════════════════════════╣
║ LAYOUT COMPONENTS (Structure & Organization)                ║
║ → Filament\Schemas\Components\*                              ║
║                                                              ║
║ Section, Grid, Fieldset, Tabs, Wizard, Group, Flex         ║
╠══════════════════════════════════════════════════════════════╣
║ DATA ENTRY COMPONENTS (Display Information)                 ║
║ → Filament\Infolists\Components\*                            ║
║                                                              ║
║ TextEntry, IconEntry, ImageEntry, ViewEntry,                ║
║ RepeatableEntry, CodeEntry, ColorEntry                       ║
╠══════════════════════════════════════════════════════════════╣
║ SCHEMA TYPE                                                  ║
║ → Filament\Schemas\Schema                                    ║
╚══════════════════════════════════════════════════════════════╝
```

## Related Documentation

- [Filament v4 Namespace Fix](../fixes/filament-v4-infolist-namespaces.md) - Detailed fix documentation
- [Filament Schemas Documentation](https://filamentphp.com/docs/4.x/schemas/getting-started)
- [Filament Infolists Documentation](https://filamentphp.com/docs/4.x/infolists/getting-started)
- [Filament v4 Upgrade Guide](https://filamentphp.com/docs/4.x/upgrade-guide)

## Changelog

**2025-12-16**
- Created Filament v4 best practices guide
- Added complete namespace reference
- Added verification commands and IDE setup
