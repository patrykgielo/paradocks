# Filament v4 Infolist Component Namespace Fix

**Date:** 2025-12-16
**Severity:** Critical - Application Crash
**Affected Files:**
- `/app/Filament/Resources/AppointmentResource/Pages/ViewAppointment.php`
- `/app/Filament/Resources/EmailSendResource.php`

## Problem

**Error:** `Class "Filament\Infolists\Components\Section" not found`

Filament v4.2.3 introduced a breaking change in namespace structure. Layout components (Section, Grid, Fieldset, Tabs, etc.) were moved from `Filament\Infolists\Components\*` to `Filament\Schemas\Components\*`.

## Root Cause

In Filament v4:
- **Layout components** (Section, Grid, Fieldset, Tabs, Wizard, etc.) moved to `Filament\Schemas\Components\*`
- **Data entry components** (TextEntry, IconEntry, ImageEntry, ViewEntry, RepeatableEntry) remain in `Filament\Infolists\Components\*`

The rector upgrade file (`vendor/filament/upgrade/src/rector.php` line 169) shows:
```php
'Filament\\Infolists\\Components\\Section' => 'Filament\\Schemas\\Components\\Section',
```

This is an upgrade mapping from v3 → v4, NOT a runtime alias.

## Investigation Details

### What We Tested

1. **Class existence check:**
   ```bash
   php -r "echo class_exists('Filament\Infolists\Components\Section') ? 'EXISTS' : 'NOT FOUND';"
   # Result: NOT FOUND
   ```

2. **Vendor directory scan:**
   ```bash
   find vendor/filament -name "Section.php" -type f
   # Result: vendor/filament/schemas/src/Components/Section.php
   ```

3. **Namespace verification:**
   ```php
   // vendor/filament/schemas/src/Components/Section.php
   namespace Filament\Schemas\Components;
   class Section extends Component { ... }
   ```

### Why It Was Confusing

EmailSendResource used this pattern (which APPEARS to work):
```php
use Filament\Infolists;  // Namespace alias

Infolists\Components\Section::make('Email Details')  // Should fail!
```

**But this actually FAILS too!** The resource was never accessed via the view page, only via the list page. The `infolist()` method in EmailSendResource was never executed in production, so the error went undetected.

## Solution

### Layout Components (use Schemas namespace)

```php
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;
```

### Data Entry Components (use Infolists namespace)

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

### Complete Example

**Before (Broken):**
```php
use Filament\Infolists\Components\Section;  // ✗ WRONG
use Filament\Infolists\Components\Grid;     // ✗ WRONG
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

public function infolist(Schema $schema): Schema
{
    return $schema->components([
        Section::make('Details')->schema([  // Class not found error!
            TextEntry::make('name'),
        ]),
    ]);
}
```

**After (Fixed):**
```php
use Filament\Schemas\Components\Section;    // ✓ CORRECT
use Filament\Schemas\Components\Grid;       // ✓ CORRECT
use Filament\Infolists\Components\TextEntry; // ✓ CORRECT
use Filament\Infolists\Components\IconEntry; // ✓ CORRECT
use Filament\Schemas\Schema;

public function infolist(Schema $schema): Schema
{
    return $schema->components([
        Section::make('Details')->schema([
            TextEntry::make('name'),
        ]),
    ]);
}
```

## Fixed Files

### ViewAppointment.php

```php
// Before
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;

// After
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
// TextEntry and IconEntry stay in Infolists namespace
```

### EmailSendResource.php

```php
// Before
use Filament\Infolists;  // Namespace alias (broken pattern)

Infolists\Components\Section::make('Email Details')  // ✗ WRONG

// After
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\RepeatableEntry;

Section::make('Email Details')  // ✓ CORRECT
```

## Verification Commands

```bash
# 1. Clear all caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan filament:optimize-clear

# 2. Restart containers to clear OPcache
docker compose restart app horizon queue scheduler

# 3. Test class loading
docker compose exec app php -r "
echo 'Layout components (Schemas):' . PHP_EOL;
echo 'Section: ' . (class_exists('Filament\Schemas\Components\Section') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'Grid: ' . (class_exists('Filament\Schemas\Components\Grid') ? 'OK' : 'FAIL') . PHP_EOL;
echo PHP_EOL . 'Data components (Infolists):' . PHP_EOL;
echo 'TextEntry: ' . (class_exists('Filament\Infolists\Components\TextEntry') ? 'OK' : 'FAIL') . PHP_EOL;
echo 'IconEntry: ' . (class_exists('Filament\Infolists\Components\IconEntry') ? 'OK' : 'FAIL') . PHP_EOL;
"

# 4. Test ViewAppointment page loading
# Access: https://paradocks.local:8444/admin/appointments/{id}
```

## Future Prevention

### Rule for Filament v4.2.3+

**ALWAYS use this namespace pattern for infolists:**

| Component Type | Namespace | Examples |
|----------------|-----------|----------|
| **Layout** | `Filament\Schemas\Components\*` | Section, Grid, Fieldset, Tabs, Wizard, Group |
| **Data Display** | `Filament\Infolists\Components\*` | TextEntry, IconEntry, ImageEntry, ViewEntry |
| **Schema** | `Filament\Schemas\Schema` | Schema class |

### Quick Check Command

```bash
# Search for potential namespace issues
grep -r "use Filament\\\\Infolists\\\\Components\\\\Section" app/ --include="*.php"
grep -r "use Filament\\\\Infolists\\\\Components\\\\Grid" app/ --include="*.php"
grep -r "use Filament\\\\Infolists\\\\Components\\\\Fieldset" app/ --include="*.php"
```

If these commands return results, fix them immediately!

## Related Documentation

- Filament v4 Upgrade Guide: https://filamentphp.com/docs/4.x/upgrade-guide
- Schemas Package: https://filamentphp.com/docs/4.x/schemas/getting-started
- Infolists Package: https://filamentphp.com/docs/4.x/infolists/getting-started

## Lessons Learned

1. **Don't trust namespace aliases** - Explicit imports are safer
2. **Always test view pages** - List pages don't trigger `infolist()` method
3. **Check vendor source code** - Official documentation may lag behind breaking changes
4. **Test after Filament upgrades** - Run full admin panel workflow tests
