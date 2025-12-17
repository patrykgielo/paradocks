# Filament v4 Component Architecture

**Version:** Filament v4.2.3
**Last Updated:** 2025-12-17
**Purpose:** Complete reference for Filament component hierarchy, nesting rules, and placement strategies

---

## Table of Contents

1. [Component Type Reference](#component-type-reference)
2. [Component Hierarchy](#component-hierarchy)
3. [Nesting Rules Matrix](#nesting-rules-matrix)
4. [Widget Architecture](#widget-architecture)
5. [Page Architecture](#page-architecture)
6. [Resource Architecture](#resource-architecture)
7. [Common Mistakes to Avoid](#common-mistakes-to-avoid)

---

## Component Type Reference

Filament v4 has 6 main component types, each with specific purposes and placement rules:

### 1. Panel Components

**Purpose:** Top-level application containers
**Namespace:** `Filament\Panel`
**Examples:** Admin panel configuration, authentication

```php
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    return $panel
        ->default()
        ->id('admin')
        ->path('admin')
        ->login();
}
```

**Characteristics:**
- Defined in `AdminPanelProvider`
- Contains: Resources, Pages, Widgets
- Cannot be nested inside other components

---

### 2. Resource Components

**Purpose:** CRUD interfaces for Eloquent models
**Namespace:** `App\Filament\Resources`
**Examples:** UserResource, AppointmentResource

```php
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function form(Form $form): Form { }
    public static function table(Table $table): Table { }
}
```

**Characteristics:**
- Contains: Forms, Tables, Pages
- Registered in Panel
- Provides complete model management

---

### 3. Page Components

**Purpose:** Custom admin pages with full control
**Namespace:** `App\Filament\Pages`
**Examples:** Dashboard, Settings, Custom pages

```php
use Filament\Pages\Page;

class SystemSettings extends Page
{
    protected static string $view = 'filament.pages.system-settings';

    // Full control over page structure
}
```

**Characteristics:**
- Can contain: Widgets, Sections, Forms
- Registered in Panel or as Resource pages
- Full layout control via Blade templates

---

### 4. Widget Components

**Purpose:** Reusable dashboard and page components
**Namespace:** `App\Filament\Widgets`
**Types:** Stats, Charts, Tables, Custom

```php
use Filament\Widgets\Widget;

class CacheClearWidget extends Widget
{
    protected string $view = 'filament.widgets.cache-clear';

    // Widgets are self-contained with built-in layout
}
```

**Characteristics:**
- Self-contained with built-in layout
- Can be placed: Dashboard, Pages, Resource pages
- **CRITICAL:** Do NOT nest Section components inside widgets

---

### 5. Form Components

**Purpose:** Data entry and display in forms
**Namespace:** `Filament\Forms\Components`
**Examples:** TextInput, Select, FileUpload

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

TextInput::make('name')->required();
Select::make('status')->options([...]);
```

**Characteristics:**
- Used inside Forms (Resources, Pages)
- Handles validation and data binding
- Can be grouped with Fieldsets, Sections, Tabs

---

### 6. Table Components

**Purpose:** Data display in tables
**Namespace:** `Filament\Tables\Components`
**Examples:** TextColumn, BadgeColumn, Actions

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

TextColumn::make('name')->searchable();
BadgeColumn::make('status')->colors([...]);
```

**Characteristics:**
- Used inside Tables (Resources, Widgets)
- Handles sorting, searching, filtering
- Can include actions and bulk actions

---

## Component Hierarchy

### Visual Hierarchy Diagram

```
Panel (AdminPanelProvider)
├── Dashboard (Page)
│   ├── Widget (Stats)
│   ├── Widget (Chart)
│   └── Widget (Table)
│
├── Resource (e.g., UserResource)
│   ├── Form
│   │   ├── Section (Layout)
│   │   │   ├── TextInput
│   │   │   ├── Select
│   │   │   └── DatePicker
│   │   └── Tabs (Layout)
│   │       ├── Tab 1 → Fields
│   │       └── Tab 2 → Fields
│   │
│   ├── Table
│   │   ├── TextColumn
│   │   ├── BadgeColumn
│   │   └── Actions
│   │
│   └── Pages
│       ├── ListRecords
│       ├── CreateRecord
│       └── EditRecord
│           └── Widget (optional)
│
└── Custom Page
    ├── Section (Layout)
    │   └── Content
    ├── Widget (Custom)
    └── Form (optional)
```

---

## Nesting Rules Matrix

### What Can Contain What

| Parent Component | Can Contain | Cannot Contain |
|-----------------|-------------|----------------|
| **Panel** | Resources, Pages, Widgets | Forms, Tables directly |
| **Resource** | Forms, Tables, Pages | Widgets directly, Sections directly |
| **Page** | Sections, Widgets, Forms | Resource components |
| **Widget** | Direct HTML, Livewire components | ❌ Section (causes layout issues) |
| **Form** | Form Components, Layout Components (Section, Tabs, Grid) | Widgets, Pages |
| **Table** | Table Columns, Actions | Form Components, Widgets |
| **Section** | Form Components, Content | Widgets, Resources |

### Critical Rules

#### ✅ CORRECT Nesting

```php
// 1. Widget with direct content (NO Section wrapper)
<x-filament-widgets::widget>
    <x-slot name="heading">Cache Management</x-slot>
    <x-slot name="description">Operations</x-slot>

    <div class="space-y-4">
        <!-- Direct content -->
    </div>
</x-filament-widgets::widget>

// 2. Page with Sections
<x-filament::page>
    <x-filament::section heading="User Settings">
        <!-- Content -->
    </x-filament::section>

    <x-filament::section heading="Notifications">
        <!-- Content -->
    </x-filament::section>
</x-filament::page>

// 3. Form with Sections
use Filament\Schemas\Components\Section;

Section::make('Personal Information')
    ->schema([
        TextInput::make('first_name'),
        TextInput::make('last_name'),
    ]);
```

#### ❌ WRONG Nesting

```php
// 1. Section nested in Widget (CAUSES LAYOUT ISSUES!)
<x-filament-widgets::widget>
    <x-filament::section>  ← WRONG! Breaks layout
        <x-slot name="heading">Title</x-slot>
        Content
    </x-filament::section>
</x-filament-widgets::widget>

// 2. Widget directly in Resource
class UserResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            CacheClearWidget::class,  ← WRONG! Use in pages only
        ]);
    }
}

// 3. Form Components in Widget Blade
<x-filament-widgets::widget>
    {{ Forms\Components\TextInput::make('name') }}  ← WRONG! Use Livewire properties
</x-filament-widgets::widget>
```

---

## Widget Architecture

### Widget Placement Strategies

#### 1. Dashboard Widgets

**Purpose:** Global metrics and actions
**Registration:** `AdminPanelProvider::widgets()`

```php
// app/Providers/Filament/AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->widgets([
            Widgets\AccountWidget::class,
            Widgets\FilamentInfoWidget::class,
            CacheClearWidget::class,  // Custom widget
        ]);
}
```

**Characteristics:**
- Visible on main dashboard
- Available to all admin users (with permissions)
- Best for: Stats, charts, quick actions

#### 2. Page-Level Widgets

**Purpose:** Page-specific functionality
**Registration:** Inside custom Page classes

```php
// app/Filament/Pages/SystemSettings.php
class SystemSettings extends Page
{
    protected function getHeaderWidgets(): array
    {
        return [
            CacheStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            SystemInfoWidget::class,
        ];
    }
}
```

**Characteristics:**
- Contextual to specific page
- Can access page data via Livewire
- Best for: Page-specific actions, related metrics

#### 3. Resource Page Widgets

**Purpose:** Model-specific functionality
**Registration:** Inside Resource page classes

```php
// app/Filament/Resources/UserResource/Pages/EditUser.php
class EditUser extends EditRecord
{
    protected function getHeaderWidgets(): array
    {
        return [
            UserActivityWidget::class,
        ];
    }
}
```

**Characteristics:**
- Access to model record via `$this->record`
- Best for: Model-specific metrics, related actions

---

### Widget Structure

#### Widget Class Structure

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CacheClearWidget extends Widget
{
    // View path (required)
    protected string $view = 'filament.widgets.cache-clear';

    // Column span (optional, default: 1)
    protected int | string | array $columnSpan = 'full';

    // Sort order (optional)
    protected static ?int $sort = 999;

    // Authorization (optional)
    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin']) ?? false;
    }

    // Livewire methods
    public function clearCache(): void
    {
        // Widget logic
    }
}
```

#### Widget Blade Template Structure

```blade
{{-- resources/views/filament/widgets/cache-clear.blade.php --}}

<x-filament-widgets::widget>
    {{-- Heading (optional) --}}
    <x-slot name="heading">
        Widget Title
    </x-slot>

    {{-- Description (optional) --}}
    <x-slot name="description">
        Widget description text
    </x-slot>

    {{-- Direct content (NO Section wrapper!) --}}
    <div class="space-y-4">
        {{-- Your widget content --}}

        {{-- Filament buttons work with wire:click --}}
        <x-filament::button
            wire:click="clearCache"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="clearCache">Clear Cache</span>
            <span wire:loading wire:target="clearCache">Clearing...</span>
        </x-filament::button>
    </div>
</x-filament-widgets::widget>
```

---

## Page Architecture

### Page Types

#### 1. Simple Pages

**Purpose:** Static content pages
**Example:** Dashboard, About, Help

```php
use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
}
```

#### 2. Form Pages

**Purpose:** Settings, configuration
**Example:** System Settings, Profile

```php
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'site_name' => settings('site_name'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Settings')
                    ->schema([
                        TextInput::make('site_name'),
                    ]),
            ])
            ->statePath('data');
    }
}
```

#### 3. Resource Pages

**Purpose:** CRUD operations
**Types:** List, Create, Edit, View

```php
// Generated by: php artisan make:filament-resource User
class ListUsers extends ListRecords { }
class CreateUser extends CreateRecord { }
class EditUser extends EditRecord { }
class ViewUser extends ViewRecord { }
```

---

## Resource Architecture

### Resource Structure

```php
<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Personal Information')
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->required(),
                    Forms\Components\TextInput::make('last_name')
                        ->required(),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required(),
                ]),

            Forms\Components\Section::make('Security')
                ->schema([
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->required(fn ($context) => $context === 'create'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```

---

## Common Mistakes to Avoid

### 1. Widget/Section Nesting ⚠️ CRITICAL

**Problem:** Nesting `<x-filament::section>` inside `<x-filament-widgets::widget>`

❌ **WRONG:**
```blade
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Title</x-slot>
        Content
    </x-filament::section>
</x-filament-widgets::widget>
```

✅ **CORRECT:**
```blade
<x-filament-widgets::widget>
    <x-slot name="heading">Title</x-slot>
    <div>Content</div>
</x-filament-widgets::widget>
```

**Why:** Widgets have built-in layout and styling. Section adds conflicting wrapper that breaks spacing, icons, and responsive behavior.

**Symptoms:**
- Giant icons appearing above heading
- Double borders
- Broken responsive layout
- Inconsistent spacing

---

### 2. Using Old Namespaces (v3 → v4)

**Problem:** Using deprecated v3 namespaces

❌ **WRONG (v3):**
```php
use Filament\Forms\Components\Section;  // Old namespace
use Filament\Forms\Components\Grid;     // Old namespace
```

✅ **CORRECT (v4):**
```php
use Filament\Schemas\Components\Section;  // Layout components
use Filament\Schemas\Components\Grid;     // Layout components
use Filament\Forms\Components\TextInput;  // Data entry components
```

**Rule:** Layout components (Section, Grid, Tabs) → `Filament\Schemas\Components`

---

### 3. Mixing Form and Infolist Components

**Problem:** Using wrong component type for display

❌ **WRONG:**
```php
// In ViewUser page (should use Infolist)
Forms\Components\TextInput::make('name')->disabled();
```

✅ **CORRECT:**
```php
// Use Infolist for read-only display
use Filament\Infolists\Components\TextEntry;

TextEntry::make('name');
```

**Rule:** Forms = input/edit, Infolists = display/view

---

### 4. Incorrect Widget Registration

**Problem:** Trying to use widgets in wrong contexts

❌ **WRONG:**
```php
// In Resource form schema
Section::make()->schema([
    CacheClearWidget::class,  // Widgets can't be in forms
]);
```

✅ **CORRECT:**
```php
// In Page or Resource Page
protected function getHeaderWidgets(): array
{
    return [CacheClearWidget::class];
}
```

---

### 5. Missing Authorization Checks

**Problem:** Widgets visible to unauthorized users

❌ **WRONG:**
```php
class SensitiveWidget extends Widget
{
    // No canView() method - visible to all
}
```

✅ **CORRECT:**
```php
class SensitiveWidget extends Widget
{
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
```

---

### 6. Static Property Errors

**Problem:** Incorrectly making properties static

❌ **WRONG:**
```php
protected static string $view = 'filament.widgets.my-widget';
// Error: Cannot redeclare non-static property
```

✅ **CORRECT:**
```php
protected string $view = 'filament.widgets.my-widget';
// $view should NOT be static
```

**Rule:** `$view`, `$columnSpan` are NOT static. Only `$sort`, `$navigationIcon`, `$navigationLabel` are static.

---

## Quick Reference

### Component Decision Tree

```
Need to display data?
├─ Editable? → Use Form with Forms\Components
└─ Read-only? → Use Infolist with Infolists\Components

Need a reusable dashboard element?
└─ Use Widget (Stats, Chart, Table, Custom)

Need layout structure?
├─ In Form/Page? → Use Schemas\Components\Section
└─ In Widget? → Use plain HTML/Tailwind (NO Section)

Need to manage a model?
└─ Use Resource with Form + Table

Need a custom page?
└─ Use Page (can contain Widgets, Sections, Forms)
```

### Namespace Quick Reference

| Component Type | Namespace |
|----------------|-----------|
| Layout (Section, Grid, Tabs) | `Filament\Schemas\Components` |
| Form Inputs (TextInput, Select) | `Filament\Forms\Components` |
| Table Columns (TextColumn) | `Filament\Tables\Columns` |
| Infolist Entries (TextEntry) | `Filament\Infolists\Components` |
| Widgets | `Filament\Widgets` |
| Pages | `Filament\Pages` |
| Resources | `Filament\Resources` |

---

## Additional Resources

- **Official Docs:** https://filamentphp.com/docs/4.x/introduction/overview
- **Migration Guide:** [filament-v4-migration-guide.md](filament-v4-migration-guide.md)
- **Best Practices:** [filament-v4-best-practices.md](filament-v4-best-practices.md)
- **Widgets Guide:** [filament-v4-widgets-guide.md](filament-v4-widgets-guide.md)
- **Quick Reference:** [filament-v4-quick-reference.md](filament-v4-quick-reference.md)

---

**Last Updated:** 2025-12-17
**Filament Version:** v4.2.3
**Maintained By:** Development Team
