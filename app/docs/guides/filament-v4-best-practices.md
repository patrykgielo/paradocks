# Filament v4 Best Practices

**Version:** Filament v4.2.3
**Last Updated:** 2025-12-17
**Purpose:** Comprehensive guide for best practices, patterns, and anti-patterns in Filament v4

---

## Table of Contents

1. [Widget Implementation Patterns](#widget-implementation-patterns)
2. [Component Composition Guidelines](#component-composition-guidelines)
3. [Performance Optimization](#performance-optimization)
4. [Security Best Practices](#security-best-practices)
5. [Testing Strategies](#testing-strategies)
6. [Code Organization](#code-organization)
7. [Common Mistakes to Avoid](#common-mistakes-to-avoid)
8. [Accessibility Guidelines](#accessibility-guidelines)

---

## Widget Implementation Patterns

### ✅ CORRECT Widget Structure

#### Dashboard Widget

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CacheClearWidget extends Widget
{
    // NON-static view property
    protected string $view = 'filament.widgets.cache-clear';

    // Column span for layout
    protected int | string | array $columnSpan = 'full';

    // Static sort order
    protected static ?int $sort = 999;

    // Authorization check
    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    // Livewire actions
    public function clearCache(): void
    {
        Cache::flush();

        Notification::make()
            ->title('Cache cleared')
            ->success()
            ->send();
    }
}
```

#### Widget Blade Template

```blade
{{-- resources/views/filament/widgets/cache-clear.blade.php --}}

<x-filament-widgets::widget>
    {{-- Use widget slots for heading/description --}}
    <x-slot name="heading">
        Cache Management
    </x-slot>

    <x-slot name="description">
        Quick cache operations for development
    </x-slot>

    {{-- Direct content (NO Section wrapper) --}}
    <div class="space-y-4">
        <div class="flex gap-2">
            <x-filament::button
                size="sm"
                color="warning"
                wire:click="clearCache"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="clearCache">
                    Clear Cache
                </span>
                <span wire:loading wire:target="clearCache">
                    Clearing...
                </span>
            </x-filament::button>
        </div>
    </div>
</x-filament-widgets::widget>
```

**Key Points:**
- ✅ Use `<x-filament-widgets::widget>` wrapper
- ✅ Heading/description in widget slots
- ✅ Direct content inside widget (no Section)
- ✅ Livewire directives (`wire:click`, `wire:loading`)
- ✅ Proper authorization check

---

### ❌ WRONG Widget Patterns

#### Mistake 1: Section Wrapper in Widget

```blade
{{-- ❌ WRONG: Causes layout issues --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Title</x-slot>
        Content
    </x-filament::section>
</x-filament-widgets::widget>
```

**Problems:**
- Giant icons appear above heading
- Double borders
- Broken responsive layout
- Inconsistent spacing

**Solution:**
Remove Section wrapper, use widget slots directly.

---

#### Mistake 2: Static `$view` Property

```php
// ❌ WRONG: $view should NOT be static
protected static string $view = 'filament.widgets.cache-clear';

// ✅ CORRECT: Non-static property
protected string $view = 'filament.widgets.cache-clear';
```

**Error:**
```
Cannot redeclare non-static Filament\Widgets\Widget::$view as static
```

---

#### Mistake 3: Missing Authorization

```php
// ❌ WRONG: No authorization check
class SensitiveDataWidget extends Widget
{
    // Widget visible to all authenticated users
}

// ✅ CORRECT: Role-based authorization
class SensitiveDataWidget extends Widget
{
    public static function canView(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
}
```

---

## Component Composition Guidelines

### Form Schema Best Practices

#### ✅ CORRECT: Organized with Sections

```php
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

public static function form(Form $form): Form
{
    return $form->schema([
        Section::make('Personal Information')
            ->description('User contact details')
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('first_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('last_name')
                        ->required()
                        ->maxLength(255),
                ]),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
            ]),

        Section::make('Security')
            ->description('Authentication settings')
            ->schema([
                TextInput::make('password')
                    ->password()
                    ->required(fn ($context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->minLength(8),
                Select::make('role')
                    ->relationship('roles', 'name')
                    ->required(),
            ]),
    ]);
}
```

**Best Practices:**
- ✅ Group related fields in Sections
- ✅ Use Grid for responsive layouts
- ✅ Add descriptions for clarity
- ✅ Conditional validation (`required(fn...)`
- ✅ Dehydration control for passwords
- ✅ Meaningful field names and labels

---

#### ❌ WRONG: Flat, Unorganized Schema

```php
// ❌ WRONG: No organization, hard to maintain
public static function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('first_name'),
        TextInput::make('last_name'),
        TextInput::make('email'),
        TextInput::make('password'),
        Select::make('role'),
        TextInput::make('phone'),
        TextInput::make('address'),
        // ... 20 more fields
    ]);
}
```

**Problems:**
- Hard to scan and understand
- Poor UX (long scrolling)
- Difficult to maintain
- No logical grouping

---

### Table Configuration Best Practices

#### ✅ CORRECT: Well-Organized Table

```php
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->description(fn ($record) => $record->email),

            TextColumn::make('role.name')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'super-admin' => 'danger',
                    'admin' => 'warning',
                    default => 'gray',
                }),

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            SelectFilter::make('role')
                ->relationship('roles', 'name')
                ->preload(),
        ])
        ->actions([
            Action::make('impersonate')
                ->icon('heroicon-o-user')
                ->visible(fn () => auth()->user()->can('impersonate'))
                ->action(fn ($record) => auth()->user()->impersonate($record)),
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ])
        ->defaultSort('created_at', 'desc');
}
```

**Best Practices:**
- ✅ Searchable key columns
- ✅ Sortable date/name columns
- ✅ Badge for status fields
- ✅ Descriptions for additional context
- ✅ Toggleable columns for less important data
- ✅ Role-based action visibility
- ✅ Sensible default sorting

---

### Infolist Best Practices

#### ✅ CORRECT: Organized Infolist

```php
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;

public function infolist(Infolist $infolist): Infolist
{
    return $infolist->schema([
        Section::make('User Information')
            ->schema([
                Grid::make(2)->schema([
                    TextEntry::make('name')
                        ->icon('heroicon-o-user'),
                    TextEntry::make('email')
                        ->icon('heroicon-o-envelope')
                        ->copyable(),
                ]),
                TextEntry::make('role.name')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'admin' => 'warning',
                        default => 'gray',
                    }),
            ]),

        Section::make('Activity')
            ->schema([
                TextEntry::make('created_at')
                    ->dateTime()
                    ->since(),
                TextEntry::make('last_login_at')
                    ->dateTime()
                    ->placeholder('Never logged in'),
            ]),
    ]);
}
```

**Best Practices:**
- ✅ Use Sections for logical grouping
- ✅ Grid for responsive layouts
- ✅ Icons for visual clarity
- ✅ Copyable for important values (email, API keys)
- ✅ Badges for status fields
- ✅ Placeholders for nullable fields
- ✅ Human-readable dates (`since()`)

---

## Performance Optimization

### Database Query Optimization

#### ✅ CORRECT: Eager Loading

```php
use Filament\Tables\Table;

public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn ($query) => $query->with(['roles', 'teams']))
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('role.name'),  // No N+1 query
            TextColumn::make('team.name'),  // No N+1 query
        ]);
}
```

**Benefits:**
- ✅ Prevents N+1 queries
- ✅ Faster page load
- ✅ Reduced database load

---

#### ❌ WRONG: N+1 Query Problem

```php
// ❌ WRONG: N+1 queries when displaying role/team
public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('role.name'),  // N+1 query!
            TextColumn::make('team.name'),  // N+1 query!
        ]);
}
```

---

### Caching Strategies

#### ✅ CORRECT: Cache Expensive Queries

```php
use Illuminate\Support\Facades\Cache;

class StatsWidget extends Widget
{
    public function getStats(): array
    {
        return Cache::remember('dashboard.stats', now()->addMinutes(5), function () {
            return [
                'total_users' => User::count(),
                'active_users' => User::where('last_login_at', '>', now()->subDays(30))->count(),
                'total_revenue' => Order::sum('total'),
            ];
        });
    }
}
```

**Benefits:**
- ✅ Reduced database queries
- ✅ Faster widget rendering
- ✅ Lower server load

**Important:** Clear cache when data changes:
```php
public function afterCreate(): void
{
    Cache::forget('dashboard.stats');
}
```

---

### Asset Optimization

#### ✅ CORRECT: Optimize Production Build

```bash
# Build for production
npm run build

# Verify output
ls -lh public/build/

# Clear Filament cache
php artisan filament:optimize
```

**vite.config.js:**
```javascript
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    'vendor': ['alpinejs', 'livewire'],
                },
            },
        },
    },
});
```

---

## Security Best Practices

### Authorization

#### ✅ CORRECT: Resource-Level Authorization

```php
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    // Policy-based authorization
    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewAny', User::class);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create', User::class);
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()->can('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()->can('delete', $record);
    }
}
```

**Benefits:**
- ✅ Uses Laravel Policies
- ✅ Consistent authorization logic
- ✅ Prevents unauthorized access

---

#### ❌ WRONG: No Authorization Checks

```php
// ❌ WRONG: All authenticated users can access
class UserResource extends Resource
{
    // No authorization methods
}
```

**Risk:** Unauthorized users can view/edit sensitive data.

---

### Input Validation

#### ✅ CORRECT: Comprehensive Validation

```php
use Filament\Forms\Components\TextInput;

TextInput::make('email')
    ->email()
    ->required()
    ->unique(table: User::class, ignoreRecord: true)
    ->maxLength(255);

TextInput::make('phone')
    ->tel()
    ->regex('/^[0-9]{9}$/')
    ->required();

TextInput::make('website')
    ->url()
    ->nullable();
```

**Best Practices:**
- ✅ Use built-in validation rules
- ✅ Regex for custom patterns
- ✅ Unique validation with `ignoreRecord` for edits
- ✅ Proper nullable handling

---

### Mass Assignment Protection

#### ✅ CORRECT: Protected Attributes

```php
// Model
class User extends Authenticatable
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
    ];

    protected $guarded = [
        'is_admin',  // Prevent mass assignment
        'role_id',   // Use relationships
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}

// Resource
public static function form(Form $form): Form
{
    return $form->schema([
        // Only allow editing via specific method
        Select::make('role')
            ->relationship('roles', 'name')
            ->visible(fn () => auth()->user()->can('assignRoles')),
    ]);
}
```

---

## Testing Strategies

### Feature Tests for Resources

#### ✅ CORRECT: Comprehensive Resource Tests

```php
<?php

use App\Models\User;
use function Pest\Laravel\{actingAs, get, post};
use function Pest\Livewire\livewire;

it('allows admin to view user list', function () {
    $admin = User::factory()->create()->assignRole('admin');

    actingAs($admin)
        ->get('/admin/users')
        ->assertSuccessful();
});

it('prevents non-admin from accessing users', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/admin/users')
        ->assertForbidden();
});

it('validates required fields on user creation', function () {
    $admin = User::factory()->create()->assignRole('admin');

    livewire(\App\Filament\Resources\UserResource\Pages\CreateUser::class)
        ->actingAs($admin)
        ->fillForm([
            'email' => 'invalid-email',  // Invalid email
        ])
        ->call('create')
        ->assertHasFormErrors(['email', 'first_name', 'last_name']);
});

it('creates user with valid data', function () {
    $admin = User::factory()->create()->assignRole('admin');

    livewire(\App\Filament\Resources\UserResource\Pages\CreateUser::class)
        ->actingAs($admin)
        ->fillForm([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(User::where('email', 'john@example.com')->exists())->toBeTrue();
});
```

---

## Code Organization

### File Structure Best Practices

```
app/Filament/
├── Resources/
│   ├── UserResource.php
│   ├── UserResource/
│   │   ├── Pages/
│   │   │   ├── ListUsers.php
│   │   │   ├── CreateUser.php
│   │   │   └── EditUser.php
│   │   └── RelationManagers/
│   │       └── RolesRelationManager.php
│   │
│   └── AppointmentResource.php
│
├── Pages/
│   ├── Dashboard.php
│   └── SystemSettings.php
│
└── Widgets/
    ├── StatsOverview.php
    ├── CacheClearWidget.php
    └── RecentActivityWidget.php
```

**Best Practices:**
- ✅ One Resource per model
- ✅ Dedicated directories for complex resources
- ✅ Separate Pages for custom functionality
- ✅ Reusable Widgets for dashboard/pages

---

## Common Mistakes to Avoid

### 1. Widget/Section Nesting ⚠️ CRITICAL

**Issue:** Most common mistake in Filament v4

❌ **WRONG:**
```blade
<x-filament-widgets::widget>
    <x-filament::section>
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

---

### 2. Forgetting Eager Loading

**Issue:** N+1 query problems

❌ **WRONG:**
```php
public static function table(Table $table): Table
{
    return $table->columns([
        TextColumn::make('user.name'),  // N+1 query
    ]);
}
```

✅ **CORRECT:**
```php
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn ($query) => $query->with('user'))
        ->columns([
            TextColumn::make('user.name'),  // Single query
        ]);
}
```

---

### 3. Missing Authorization Checks

**Issue:** Security vulnerability

❌ **WRONG:**
```php
class SensitiveDataResource extends Resource
{
    // No authorization
}
```

✅ **CORRECT:**
```php
class SensitiveDataResource extends Resource
{
    public static function canViewAny(): bool
    {
        return auth()->user()->can('viewSensitiveData');
    }
}
```

---

### 4. Hardcoded Values

**Issue:** Difficult to maintain

❌ **WRONG:**
```php
Select::make('status')->options([
    'active' => 'Active',
    'inactive' => 'Inactive',
]);
```

✅ **CORRECT:**
```php
// In Enum
enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::INACTIVE => __('Inactive'),
        };
    }
}

// In Resource
Select::make('status')
    ->options(UserStatus::class)
    ->required();
```

---

### 5. Not Clearing Cache

**Issue:** Old data persists

❌ **WRONG:**
```php
public function updateSettings(): void
{
    settings()->set('site_name', $this->form->getState()['site_name']);
    // Cache not cleared - old value persists
}
```

✅ **CORRECT:**
```php
public function updateSettings(): void
{
    settings()->set('site_name', $this->form->getState()['site_name']);
    Cache::tags('settings')->flush();

    Notification::make()
        ->title('Settings updated')
        ->success()
        ->send();
}
```

---

## Accessibility Guidelines

### WCAG 2.2 AA Compliance

#### ✅ CORRECT: Accessible Forms

```php
TextInput::make('email')
    ->label('Email Address')  // Explicit label
    ->helperText('We will never share your email')  // Helper text
    ->required()
    ->email();

Select::make('country')
    ->label('Country')
    ->options(Country::pluck('name', 'code'))
    ->searchable()  // Keyboard navigation
    ->required();
```

**Best Practices:**
- ✅ Always provide labels
- ✅ Use helper text for complex fields
- ✅ Enable searchable for long lists
- ✅ Proper ARIA attributes (handled by Filament)

---

## Quick Reference Checklist

### Before Deploying to Production

- [ ] All Resources have authorization checks
- [ ] Forms use proper validation
- [ ] Tables use eager loading for relationships
- [ ] Widgets have no Section wrappers
- [ ] Cache cleared after settings changes
- [ ] Production assets built (`npm run build`)
- [ ] Filament optimized (`php artisan filament:optimize`)
- [ ] Tests passing
- [ ] No console errors
- [ ] WCAG AA compliance verified

---

## Additional Resources

- **Component Architecture:** [filament-v4-component-architecture.md](filament-v4-component-architecture.md)
- **Migration Guide:** [filament-v4-migration-guide.md](filament-v4-migration-guide.md)
- **Widgets Guide:** [filament-v4-widgets-guide.md](filament-v4-widgets-guide.md)
- **Official Docs:** https://filamentphp.com/docs/4.x/introduction/overview

---

**Last Updated:** 2025-12-17
**Filament Version:** v4.2.3
**Maintained By:** Development Team
