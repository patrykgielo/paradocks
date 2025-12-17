# Filament v4 Widgets Guide

**Version:** Filament v4.2.3
**Last Updated:** 2025-12-17
**Purpose:** Complete guide for creating and using widgets in Filament v4

---

## Table of Contents

1. [Widget Types Overview](#widget-types-overview)
2. [Stats Widgets](#stats-widgets)
3. [Chart Widgets](#chart-widgets)
4. [Table Widgets](#table-widgets)
5. [Custom Widgets](#custom-widgets)
6. [Placement Strategies](#placement-strategies)
7. [Layout Patterns](#layout-patterns)
8. [Data Fetching Best Practices](#data-fetching-best-practices)
9. [Livewire Integration](#livewire-integration)
10. [Performance Considerations](#performance-considerations)

---

## Widget Types Overview

Filament v4 provides 4 main widget types:

| Widget Type | Purpose | Use Case | Complexity |
|------------|---------|----------|-----------|
| **Stats** | Display key metrics | Dashboard KPIs, quick insights | Low |
| **Chart** | Visualize data trends | Analytics, time series data | Medium |
| **Table** | List recent records | Latest orders, recent users | Medium |
| **Custom** | Any custom content | Actions, forms, custom layouts | High |

### Quick Comparison

```
Stats Widget:     [📊 Total Users: 1,234 ↑ 12%]
Chart Widget:     [📈 Line/Bar/Pie chart visualization]
Table Widget:     [📋 Recent orders table with actions]
Custom Widget:    [🎨 Anything: forms, buttons, custom HTML]
```

---

## Stats Widgets

### Basic Stats Widget

#### Create Widget

```bash
php artisan make:filament-widget StatsOverview --stats
```

#### Widget Class

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\User;
use App\Models\Order;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered accounts')
                ->descriptionIcon('heroicon-o-users')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Sparkline data

            Stat::make('Total Revenue', '$' . number_format(Order::sum('total'), 2))
                ->description('All-time revenue')
                ->descriptionIcon('heroicon-o-currency-dollar')
                ->color('primary'),

            Stat::make('Active Orders', Order::where('status', 'processing')->count())
                ->description('Currently processing')
                ->descriptionIcon('heroicon-o-shopping-cart')
                ->color('warning'),
        ];
    }
}
```

**Registration:**

```php
// app/Providers/Filament/AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel->widgets([
        \App\Filament\Widgets\StatsOverview::class,
    ]);
}
```

---

### Advanced Stats Widget

#### With Increase/Decrease Indicators

```php
protected function getStats(): array
{
    $currentMonthUsers = User::whereMonth('created_at', now()->month)->count();
    $lastMonthUsers = User::whereMonth('created_at', now()->subMonth()->month)->count();
    $increase = $currentMonthUsers - $lastMonthUsers;
    $increasePercentage = $lastMonthUsers > 0
        ? round(($increase / $lastMonthUsers) * 100, 1)
        : 0;

    return [
        Stat::make('New Users This Month', $currentMonthUsers)
            ->description($increasePercentage . '% increase')
            ->descriptionIcon($increase >= 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
            ->color($increase >= 0 ? 'success' : 'danger')
            ->chart([15, 18, 22, 19, 25, 28, $currentMonthUsers]),
    ];
}
```

#### With Polling (Live Updates)

```php
class StatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';  // Update every 10 seconds

    protected function getStats(): array
    {
        return [
            Stat::make('Active Sessions', Cache::get('active_sessions', 0))
                ->description('Currently online')
                ->color('success'),
        ];
    }
}
```

---

## Chart Widgets

### Line Chart Widget

#### Create Widget

```bash
php artisan make:filament-widget UserRegistrationsChart --chart
```

#### Widget Class

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\User;
use Carbon\Carbon;

class UserRegistrationsChart extends ChartWidget
{
    protected static ?string $heading = 'User Registrations';

    protected static ?string $description = 'Monthly user growth';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = $this->getRegistrationsPerMonth();

        return [
            'datasets' => [
                [
                    'label' => 'Users registered',
                    'data' => $data['counts'],
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getRegistrationsPerMonth(): array
    {
        $months = collect(range(1, 12))->map(function ($month) {
            $date = Carbon::create(now()->year, $month, 1);
            return [
                'label' => $date->format('M'),
                'count' => User::whereYear('created_at', now()->year)
                    ->whereMonth('created_at', $month)
                    ->count(),
            ];
        });

        return [
            'labels' => $months->pluck('label')->toArray(),
            'counts' => $months->pluck('count')->toArray(),
        ];
    }
}
```

---

### Bar Chart Widget

```php
class RevenueByServiceChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue by Service';

    protected function getData(): array
    {
        $services = Service::withCount(['appointments as revenue' => function ($query) {
            $query->select(DB::raw('SUM(total_price)'));
        }])->get();

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $services->pluck('revenue')->toArray(),
                    'backgroundColor' => [
                        '#f59e0b',
                        '#10b981',
                        '#3b82f6',
                        '#8b5cf6',
                    ],
                ],
            ],
            'labels' => $services->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
```

---

### Pie Chart Widget

```php
class AppointmentStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Appointments by Status';

    protected function getData(): array
    {
        $statuses = [
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
        ];

        return [
            'datasets' => [
                [
                    'data' => array_values($statuses),
                    'backgroundColor' => ['#f59e0b', '#10b981', '#3b82f6', '#ef4444'],
                ],
            ],
            'labels' => ['Pending', 'Confirmed', 'Completed', 'Cancelled'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
```

---

### Interactive Chart with Filters

```php
class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue';

    public ?string $filter = 'month';  // Default filter

    protected function getData(): array
    {
        $data = match ($this->filter) {
            'week' => $this->getWeeklyRevenue(),
            'month' => $this->getMonthlyRevenue(),
            'year' => $this->getYearlyRevenue(),
        };

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $data['values'],
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
            'year' => 'This year',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
```

---

## Table Widgets

### Basic Table Widget

#### Create Widget

```bash
php artisan make:filament-widget LatestOrders --table
```

#### Widget Class

```php
<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Order;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'pending',
                        'success' => 'completed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (Order $record): string => route('filament.admin.resources.orders.view', $record))
                    ->icon('heroicon-o-eye'),
            ]);
    }
}
```

---

### Table Widget with Eager Loading

```php
public function table(Table $table): Table
{
    return $table
        ->query(
            Order::query()
                ->with(['customer', 'items'])  // Prevent N+1 queries
                ->latest()
                ->limit(10)
        )
        ->columns([
            Tables\Columns\TextColumn::make('customer.name'),
            Tables\Columns\TextColumn::make('items_count')
                ->counts('items'),  // Efficient count
        ]);
}
```

---

## Custom Widgets

### Custom Widget Structure

#### Create Widget

```bash
php artisan make:filament-widget CacheClearWidget
```

#### Widget Class

```php
<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CacheClearWidget extends Widget
{
    protected string $view = 'filament.widgets.cache-clear';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 999;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    public function clearApplicationCache(): void
    {
        try {
            Cache::flush();

            activity()
                ->causedBy(auth()->user())
                ->withProperties(['cache_type' => 'application'])
                ->log('Cleared application cache');

            Notification::make()
                ->title('Application cache cleared')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Cache clear failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function clearConfigCache(): void
    {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        Notification::make()
            ->title('Config cache cleared')
            ->success()
            ->send();
    }
}
```

#### Widget Blade Template

```blade
{{-- resources/views/filament/widgets/cache-clear.blade.php --}}

<x-filament-widgets::widget>
    <x-slot name="heading">
        Cache Management
    </x-slot>

    <x-slot name="description">
        Quick cache operations for development and troubleshooting
    </x-slot>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 dark:border-gray-700">
                <tr class="text-left">
                    <th class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300">Cache Layer</th>
                    <th class="py-2 pr-4 font-medium text-gray-700 dark:text-gray-300">Contains</th>
                    <th class="py-2 text-right font-medium text-gray-700 dark:text-gray-300">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr>
                    <td class="py-3 pr-4">
                        <span class="font-medium text-gray-900 dark:text-gray-100">Application</span>
                    </td>
                    <td class="py-3 pr-4 text-xs text-gray-600 dark:text-gray-400">
                        Settings, service areas, bookings
                    </td>
                    <td class="py-3 text-right">
                        <x-filament::button
                            size="sm"
                            color="warning"
                            wire:click="clearApplicationCache"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="clearApplicationCache">Clear</span>
                            <span wire:loading wire:target="clearApplicationCache">...</span>
                        </x-filament::button>
                    </td>
                </tr>

                <tr>
                    <td class="py-3 pr-4">
                        <span class="font-medium text-gray-900 dark:text-gray-100">Config</span>
                    </td>
                    <td class="py-3 pr-4 text-xs text-gray-600 dark:text-gray-400">
                        Configuration, routes, views
                    </td>
                    <td class="py-3 text-right">
                        <x-filament::button
                            size="sm"
                            color="warning"
                            wire:click="clearConfigCache"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="clearConfigCache">Clear</span>
                            <span wire:loading wire:target="clearConfigCache">...</span>
                        </x-filament::button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</x-filament-widgets::widget>
```

---

## Placement Strategies

### Dashboard Widgets

**Registration:**

```php
// app/Providers/Filament/AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel->widgets([
        Widgets\AccountWidget::class,
        \App\Filament\Widgets\StatsOverview::class,
        \App\Filament\Widgets\RevenueChart::class,
        \App\Filament\Widgets\LatestOrders::class,
    ]);
}
```

**Layout Control:**

```php
class StatsOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';  // Full width
    protected static ?int $sort = 1;  // Display order
}

class RevenueChart extends ChartWidget
{
    protected int | string | array $columnSpan = [
        'md' => 2,  // 2 columns on medium screens
        'xl' => 3,  // 3 columns on extra-large screens
    ];
    protected static ?int $sort = 2;
}
```

---

### Page-Level Widgets

**In Custom Page:**

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SystemSettings extends Page
{
    protected static string $view = 'filament.pages.system-settings';

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CacheClearWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\SystemInfoWidget::class,
        ];
    }

    protected function getHeaderWidgetsColumns(): int | array
    {
        return 2;  // Display widgets in 2 columns
    }
}
```

---

### Resource Page Widgets

**In Resource Edit Page:**

```php
<?php

namespace App\Filament\Resources\UserResource\Pages;

use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            UserResource\Widgets\UserActivityWidget::class,
        ];
    }

    // Pass record to widget
    protected function getHeaderWidgetData(): array
    {
        return [
            'record' => $this->record,
        ];
    }
}
```

**Widget Accessing Record:**

```php
class UserActivityWidget extends Widget
{
    public $record;  // Receives from getHeaderWidgetData()

    protected string $view = 'filament.widgets.user-activity';

    public function getActivities()
    {
        return $this->record->activities()->latest()->limit(10)->get();
    }
}
```

---

## Layout Patterns

### Responsive Grid Layout

```php
class Dashboard extends Page
{
    protected function getWidgets(): array
    {
        return [
            StatsOverview::class,      // columnSpan: 'full'
            RevenueChart::class,       // columnSpan: ['md' => 2]
            OrdersChart::class,        // columnSpan: ['md' => 2]
            LatestOrders::class,       // columnSpan: 'full'
        ];
    }

    protected function getColumns(): int | array
    {
        return [
            'md' => 2,  // 2 columns on medium screens
            'xl' => 4,  // 4 columns on extra-large screens
        ];
    }
}
```

**Result:**
```
Desktop (xl):
┌─────────────────────────────────────┐
│      StatsOverview (full width)     │
├─────────────┬─────────────┬─────────┤
│ RevenueChart│ OrdersChart │   ...   │
├─────────────────────────────────────┤
│    LatestOrders (full width)        │
└─────────────────────────────────────┘

Mobile:
┌─────────────┐
│ StatsOverview│
├─────────────┤
│ RevenueChart│
├─────────────┤
│ OrdersChart │
├─────────────┤
│LatestOrders │
└─────────────┘
```

---

## Data Fetching Best Practices

### Caching Expensive Queries

```php
class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = Cache::remember('dashboard.stats', now()->addMinutes(5), function () {
            return [
                'total_users' => User::count(),
                'total_revenue' => Order::sum('total'),
                'active_orders' => Order::where('status', 'processing')->count(),
            ];
        });

        return [
            Stat::make('Total Users', $stats['total_users']),
            Stat::make('Total Revenue', '$' . number_format($stats['total_revenue'], 2)),
            Stat::make('Active Orders', $stats['active_orders']),
        ];
    }
}
```

**Clear cache when data changes:**

```php
// In Order model
protected static function booted(): void
{
    static::saved(fn () => Cache::forget('dashboard.stats'));
    static::deleted(fn () => Cache::forget('dashboard.stats'));
}
```

---

### Lazy Loading Widgets

```php
class ExpensiveDataWidget extends Widget
{
    protected static bool $isLazy = true;  // Load after page render

    protected string $view = 'filament.widgets.expensive-data';

    public function getData()
    {
        // Expensive query - only runs when widget loads
        return HeavyModel::with('relations')->get();
    }
}
```

---

## Livewire Integration

### Refreshing Widgets

#### Auto-Refresh with Polling

```php
class LiveStatsWidget extends BaseWidget
{
    protected static ?string $pollingInterval = '10s';  // Refresh every 10 seconds

    protected function getStats(): array
    {
        return [
            Stat::make('Active Users', Cache::get('active_users_count', 0)),
        ];
    }
}
```

#### Manual Refresh

```php
class ManualRefreshWidget extends Widget
{
    protected string $view = 'filament.widgets.manual-refresh';

    public $data = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->data = ExpensiveQuery::get();
    }

    public function refresh(): void
    {
        $this->loadData();

        Notification::make()
            ->title('Data refreshed')
            ->success()
            ->send();
    }
}
```

**Blade Template:**

```blade
<x-filament-widgets::widget>
    <x-slot name="heading">
        Manual Refresh Widget
        <x-filament::button
            size="xs"
            wire:click="refresh"
            wire:loading.attr="disabled"
            class="ml-auto"
        >
            Refresh
        </x-filament::button>
    </x-slot>

    <div wire:loading.class="opacity-50">
        @foreach($data as $item)
            <div>{{ $item->name }}</div>
        @endforeach
    </div>
</x-filament-widgets::widget>
```

---

### Widget Communication

#### Event Listeners

```php
class ListenersWidget extends Widget
{
    protected $listeners = ['orderCreated' => 'refreshStats'];

    public function refreshStats(): void
    {
        // Reload widget data
        $this->dispatch('$refresh');
    }
}

// Emit event from another component
$this->dispatch('orderCreated');
```

---

## Performance Considerations

### Optimization Checklist

- [ ] **Cache expensive queries** (5-15 minute TTL recommended)
- [ ] **Use eager loading** in table widgets to prevent N+1
- [ ] **Lazy load widgets** with `$isLazy = true` for heavy widgets
- [ ] **Limit table rows** (5-10 rows for dashboard widgets)
- [ ] **Use polling sparingly** (30s+ intervals, not 5s)
- [ ] **Clear cache on model changes** to prevent stale data
- [ ] **Index database columns** used in widget queries

### Performance Anti-Patterns

❌ **WRONG: No Caching**
```php
protected function getStats(): array
{
    return [
        Stat::make('Total', Model::count()),  // Runs on every page load
    ];
}
```

✅ **CORRECT: Cached**
```php
protected function getStats(): array
{
    $count = Cache::remember('model.count', 300, fn () => Model::count());
    return [Stat::make('Total', $count)];
}
```

❌ **WRONG: N+1 Queries**
```php
public function table(Table $table): Table
{
    return $table->query(Order::latest());
    // columns reference $record->customer->name → N+1
}
```

✅ **CORRECT: Eager Loading**
```php
public function table(Table $table): Table
{
    return $table->query(Order::with('customer')->latest());
}
```

---

## Quick Reference

### Widget Property Reference

| Property | Type | Purpose | Default |
|----------|------|---------|---------|
| `$view` | string | Blade template path | (required) |
| `$columnSpan` | int\|string\|array | Grid columns occupied | 1 |
| `$sort` | int | Display order | 0 |
| `$pollingInterval` | string | Auto-refresh interval | null |
| `$isLazy` | bool | Lazy load widget | false |

### Common Methods

```php
canView(): bool              // Authorization
getStats(): array            // Stats widget data
getData(): array             // Chart widget data
getType(): string            // Chart type (line/bar/pie)
table(Table $table): Table   // Table widget configuration
```

---

## Additional Resources

- **Component Architecture:** [filament-v4-component-architecture.md](filament-v4-component-architecture.md)
- **Best Practices:** [filament-v4-best-practices.md](filament-v4-best-practices.md)
- **Migration Guide:** [filament-v4-migration-guide.md](filament-v4-migration-guide.md)
- **Official Docs:** https://filamentphp.com/docs/4.x/widgets/getting-started

---

**Last Updated:** 2025-12-17
**Filament Version:** v4.2.3
**Maintained By:** Development Team
