<?php

namespace App\Filament\Pages;

use App\Models\StaffSchedule;
use App\Models\StaffDateException;
use App\Models\StaffVacationPeriod;
use App\Models\User;
use App\Services\MigrationTrackerService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class StaffScheduleCalendar extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Harmonogramy';

    protected static ?string $navigationLabel = 'Harmonogramy';

    protected static ?string $title = 'Harmonogramy Pracowników';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.staff-schedule-calendar';

    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?int $selectedStaff = null;

    public function mount(): void
    {
        // Set default date range (current week)
        $this->startDate = now()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->endOfWeek()->format('Y-m-d');

        // Track migration usage
        try {
            app(MigrationTrackerService::class)->recordMigration(
                name: 'UI-MIGRATION-001-staff-scheduling',
                type: 'ui_consolidation',
                details: [
                    'hidden_resources' => [
                        'StaffScheduleResource',
                        'StaffDateExceptionResource',
                        'ServiceAvailabilityResource',
                    ],
                    'new_pages' => [
                        'StaffScheduleCalendar',
                    ],
                    'database_changes' => 'none (only ui_migrations table added)',
                    'rollback_available' => true,
                    'accessed_at' => now()->toDateTimeString(),
                ]
            );
        } catch (\Exception $e) {
            // Silent fail - don't break page if tracking fails
            \Log::warning('Failed to track migration usage', ['error' => $e->getMessage()]);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('staff_name')
                    ->label('Pracownik')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Data')
                    ->date('Y-m-d (D)')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'base' => 'primary',
                        'exception' => 'warning',
                        'vacation' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'base' => 'Harmonogram Bazowy',
                        'exception' => 'Wyjątek',
                        'vacation' => 'Urlop',
                        default => $state,
                    }),

                TextColumn::make('time_range')
                    ->label('Godziny')
                    ->formatStateUsing(fn ($record): string =>
                        $record->start_time && $record->end_time
                            ? Carbon::parse($record->start_time)->format('H:i') . ' - ' . Carbon::parse($record->end_time)->format('H:i')
                            : 'Cały dzień'
                    ),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($record): string =>
                        $record->is_available ? 'Dostępny' : 'Niedostępny'
                    )
                    ->color(fn ($record): string =>
                        $record->is_available ? 'success' : 'danger'
                    ),

                TextColumn::make('reason')
                    ->label('Powód')
                    ->placeholder('-')
                    ->limit(40)
                    ->tooltip(function (TextColumn $column, $record): ?string {
                        $reason = $record->reason ?? $record->exception_type ?? null;
                        return $reason && strlen($reason) > 40 ? $reason : null;
                    }),
            ])
            ->filters([
                //
            ])
            ->defaultSort('date', 'asc');
    }

    protected function getTableQuery()
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : now()->startOfWeek();
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : now()->endOfWeek();

        $schedules = $this->getBaseSchedules($startDate, $endDate);
        $exceptions = $this->getExceptions($startDate, $endDate);
        $vacations = $this->getVacations($startDate, $endDate);

        // Merge all events
        $allEvents = $schedules->merge($exceptions)->merge($vacations);

        // Filter by selected staff if applicable
        if ($this->selectedStaff) {
            $allEvents = $allEvents->where('staff_id', $this->selectedStaff);
        }

        // Return Collection directly - Filament 3 handles pagination automatically
        return $allEvents;
    }

    protected function getBaseSchedules(Carbon $startDate, Carbon $endDate): Collection
    {
        $schedules = StaffSchedule::with('user')
            ->active()
            ->get();

        $events = collect([]);

        // Generate events for each day in range
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dayOfWeek = $currentDate->dayOfWeek;

            foreach ($schedules as $schedule) {
                if ($schedule->day_of_week == $dayOfWeek && $schedule->isEffectiveOn($currentDate)) {
                    $events->push((object) [
                        'staff_id' => $schedule->user_id,
                        'staff_name' => $schedule->user->name,
                        'date' => $currentDate->format('Y-m-d'),
                        'type' => 'base',
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'is_available' => true,
                        'reason' => null,
                        'exception_type' => null,
                    ]);
                }
            }

            $currentDate->addDay();
        }

        return $events;
    }

    protected function getExceptions(Carbon $startDate, Carbon $endDate): Collection
    {
        $exceptions = StaffDateException::with('user')
            ->whereBetween('exception_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get();

        return $exceptions->map(function ($exception) {
            return (object) [
                'staff_id' => $exception->user_id,
                'staff_name' => $exception->user->name,
                'date' => $exception->exception_date,
                'type' => 'exception',
                'start_time' => $exception->start_time,
                'end_time' => $exception->end_time,
                'is_available' => $exception->exception_type === StaffDateException::TYPE_AVAILABLE,
                'reason' => $exception->reason,
                'exception_type' => $exception->exception_type,
            ];
        });
    }

    protected function getVacations(Carbon $startDate, Carbon $endDate): Collection
    {
        $vacations = StaffVacationPeriod::with('user')
            ->approved()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                          ->where('end_date', '>=', $endDate->format('Y-m-d'));
                    });
            })
            ->get();

        $events = collect([]);

        foreach ($vacations as $vacation) {
            $vacationStart = Carbon::parse($vacation->start_date);
            $vacationEnd = Carbon::parse($vacation->end_date);

            // Generate event for each day in vacation period (within our date range)
            $currentDate = max($vacationStart, $startDate->copy());
            $endDateLimit = min($vacationEnd, $endDate->copy());

            while ($currentDate->lte($endDateLimit)) {
                $events->push((object) [
                    'staff_id' => $vacation->user_id,
                    'staff_name' => $vacation->user->name,
                    'date' => $currentDate->format('Y-m-d'),
                    'type' => 'vacation',
                    'start_time' => null,
                    'end_time' => null,
                    'is_available' => false,
                    'reason' => $vacation->reason,
                    'exception_type' => null,
                ]);

                $currentDate->addDay();
            }
        }

        return $events;
    }

    protected function getHeaderActions(): array
    {
        return [
            // Future: Add actions like "Export", "Print", etc.
        ];
    }
}
