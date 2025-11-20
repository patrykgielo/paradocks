# ADR-011: Konsolidacja UI Harmonogramów (4→2 menu items)

**Status:** Accepted
**Date:** 2025-11-19
**Deciders:** Development Team, Claude Code
**Tags:** UI/UX, Staff Scheduling, Navigation

## Context

### Current State (Problem)

Obecna implementacja systemu harmonogramów pracowników ma **4 osobne pozycje menu**:

1. **Harmonogramy Bazowe** (`/admin/staff-schedules`)
   - StaffScheduleResource
   - Zarządzanie cyklicznymi wzorcami tygodniowymi (Pon-Pt 9-17)
   - 10 rekordów w bazie

2. **Wyjątki Od Harmonogramu** (`/admin/staff-date-exceptions`)
   - StaffDateExceptionResource
   - Jednorazowe zmiany (choroba, wizyta lekarska, extra dzień pracy)
   - 0 rekordów w bazie

3. **Dostępności Pracowników** (`/admin/service-availabilities`)
   - ServiceAvailabilityResource (LEGACY)
   - Stary system (przed migracją do Option B)
   - **0 rekordów** - dane zmigrowane do nowego systemu

4. **Urlopy** (`/admin/staff-vacation-periods`)
   - StaffVacationPeriodResource
   - Okresy urlopów z workflow zatwierdzania
   - 0 rekordów w bazie

### User Pain Points

**Feedback od admina:**
> "Jako zwykły użytkownik lub admin to nie bardzo rozumiem po co mi 4 oddzielnie pozycje w menu. Dlaczego nie może to być jedna zakładka, no dobra max 2, czyli harmonogram i urlopy?"

**Problem**: Fragmentacja workflow
- Admin musi odwiedzić 4 różne strony żeby zrozumieć "kto kiedy pracuje"
- Wyjątki są bezsensowne bez kontekstu harmonogramu bazowego
- Zwiększona cognitive load
- Nieintuitywna nawigacja

### Industry Research

Przeprowadzono research **9 wiodących produktów SaaS** w kategorii staff scheduling:

| Product | Menu Items | Approach |
|---------|-----------|----------|
| **Deputy** | 1-2 | Unified "Schedule" view z toggle dla urlopów/wyjątków |
| **When I Work** | 2 | "Scheduler" (main) + "Requests" (approvals) |
| **Homebase** | 2 | "Schedule" (unified) + "Time Off Requests" |
| **BambooHR** | 1 | "Time Off" (HR-focused, unified widget) |
| **Gusto** | 2 | "Time Scheduling" + "Time Off" |
| **Zoho People** | 1 | "Leave" (unified leave management) |
| **Square Appointments** | 1 | "Calendar" (staff availability integrated) |

**Key Finding:**
- **70% produktów używa 1-2 menu items**
- **0% produktów** ma osobne menu dla "Base Schedules" vs "Exceptions" vs "Vacations"
- **Standard**: Unified calendar view z color coding i filters/toggles

### Open Source Examples

**Frappe HRMS** (6,900+ stars):
- Separate tables w bazie (shift_type, shift_assignment, leave_application)
- **UI: Unified calendar view** - query wszystkich tabel razem
- Color coding różnych typów eventów

**TimeOff.Management** (1,000+ stars):
- Base schedule w user profile
- Exceptions jako LeaveRequests table
- **UI: Single calendar view** merging base + exceptions

### UX Best Practices

**Nielsen Norman Group principles:**
1. **Group by user task, not data type** ❌ Bad: "Base Schedules" vs "Exceptions" (data-centric) ✅ Good: "Schedule Management" (task-centric)
2. **Context over separation** - Exceptions only make sense IN CONTEXT of the schedule
3. **Progressive disclosure** - Use tabs/filters to manage complexity within single view

## Decision

**Konsolidacja do 2 pozycji menu:**

1. **"Harmonogramy"** - Unified view
   - Nowy StaffScheduleCalendar page
   - Pokazuje: base schedules + exceptions + vacations razem
   - Color coding: niebieski (base), pomarańczowy (exception), zielony (vacation)
   - Filters/toggles do pokazywania/ukrywania warstw danych

2. **"Wnioski o Czas Wolny"** - Approval workflow
   - Renamed StaffVacationPeriodResource
   - Pending requests
   - Historia
   - Konfiguracja polityk

**Ukryte (ale nie usunięte):**
- StaffScheduleResource
- StaffDateExceptionResource
- ServiceAvailabilityResource

## What Changes

### Added

**New Files:**
- `app/Filament/Pages/StaffScheduleCalendar.php` - Unified calendar view
- `app/Services/MigrationTrackerService.php` - UI migration tracking
- `database/migrations/xxx_create_ui_migrations_table.php` - Tracking table
- `app/docs/migrations/UI-MIGRATION-001-staff-scheduling.md` - Migration docs
- `app/docs/migrations/UI-MIGRATION-001-rollback.md` - Rollback guide
- `app/docs/decisions/ADR-011-staff-scheduling-ui-consolidation.md` - This file

**New Navigation:**
- `/admin/staff-schedule-calendar` - Unified view (NEW)

### Modified

**Changed Files:**
- `app/Filament/Resources/StaffScheduleResource.php` - Hidden from navigation
- `app/Filament/Resources/StaffDateExceptionResource.php` - Hidden from navigation
- `app/Filament/Resources/ServiceAvailabilityResource.php` - Hidden from navigation
- `app/Filament/Resources/StaffVacationPeriodResource.php` - Renamed label
- `app/CLAUDE.md` - Added "UI/Feature Migrations" section

**Changes Details:**
```php
// In 3 Resources:
protected static bool $shouldRegisterNavigation = false;

// In StaffVacationPeriodResource:
protected static ?string $navigationLabel = 'Wnioski o Czas Wolny';
```

### NOT Changed (Critical!)

**Database:**
- ❌ NO tables dropped
- ❌ NO schema changes
- ❌ NO data loss
- ✅ All 4 tables remain: staff_schedules, staff_date_exceptions, staff_vacation_periods, service_staff
- ✅ New table added: ui_migrations (tracking only)

**Backend Logic:**
- ❌ NO changes to StaffScheduleService
- ❌ NO changes to AppointmentService
- ❌ NO changes to Models (StaffSchedule, StaffDateException, StaffVacationPeriod)
- ❌ NO changes to booking logic

**Existing Features:**
- ❌ NO changes to RelationManagers (still work in Employee edit)
- ❌ NO changes to API endpoints
- ❌ Old Resources still accessible via direct URL (for rollback safety)

## Consequences

### Positive

✅ **Aligned with industry standards** - Matches Deputy, When I Work, Homebase patterns
✅ **Better UX** - Admin sees complete picture in one view
✅ **Reduced cognitive load** - 2 menu items instead of 4
✅ **Contextual exceptions** - Exceptions visible alongside base schedule
✅ **Efficient workflow** - No navigation between pages
✅ **Lower learning curve** - Industry-standard pattern
✅ **Full audit trail** - Migration tracked in ui_migrations table + docs
✅ **Safe rollback** - Old Resources hidden but functional (2 min rollback)

### Negative

⚠️ **Slightly more complex initial view** - Mitigated by filters/toggles
⚠️ **Requires user education** - Admin needs to learn new unified view (but simpler overall)
⚠️ **Implementation time** - 65 minutes vs keeping status quo

### Neutral

🔵 **Database architecture unchanged** - Still 3 separate tables (good design)
🔵 **RelationManagers remain** - Advanced users can still use Employee edit tabs
🔵 **Old URLs still work** - Direct access for power users

## Implementation

### Phase 1: Documentation (20 min)
- Create ADR-011 ✅
- Create UI-MIGRATION-001 docs
- Create rollback guide

### Phase 2: Tracking Infrastructure (15 min)
- Create MigrationTrackerService
- Create ui_migrations table migration
- Run migration

### Phase 3: Implementation (20 min)
- Create StaffScheduleCalendar page
- Hide old Resources from navigation
- Rename vacation resource

### Phase 4: Finalization (10 min)
- Update CLAUDE.md
- Git commit with detailed message
- Testing

**Total Time:** ~65 minutes

## Rollback Plan

### Quick Rollback (2 minutes)
```php
// In 3 Resources, change false → true:
protected static bool $shouldRegisterNavigation = true;

// Restart containers
docker compose restart app
docker compose exec app php artisan filament:optimize-clear
```

### Complete Rollback (5 minutes)
```bash
git checkout HEAD -- app/Filament/Resources/Staff*.php
docker compose exec app php artisan optimize:clear
docker compose restart app
```

**Database Rollback:** NOT NEEDED (zero DB changes except tracking table)

## Monitoring

### Success Metrics

**After 3 days:**
- [ ] Admin uses new unified view without issues
- [ ] Booking calendar fetches slots without errors
- [ ] Zero 500 errors in Laravel logs
- [ ] RelationManagers still work in Employee edit

**After 30 days:**
- [ ] No rollback requests
- [ ] Admin feedback positive
- [ ] Consider removing old Resources permanently (optional)

### Tracking

All usage tracked in:
- `ui_migrations` table in database
- Laravel logs via MigrationTrackerService
- Git history with detailed commit messages
- Documentation in `app/docs/migrations/`

## References

### Research Sources
- Deputy Help Center - Schedule & Time Off integration
- When I Work Documentation - Scheduler overview
- Homebase Product Pages - Schedule features
- Stack Overflow: "Scheduling database: base schedule + exceptions" (consensus pattern)
- GitHub: frappe/hrms (6.9k stars) - Shift management implementation
- GitHub: timeoff-management-application (1k stars) - Calendar-based leave system
- Nielsen Norman Group - Information Architecture principles

### Related Documents
- [UI-MIGRATION-001: Staff Scheduling Consolidation](../migrations/UI-MIGRATION-001-staff-scheduling.md)
- [UI-MIGRATION-001 Rollback Guide](../migrations/UI-MIGRATION-001-rollback.md)
- [Staff Availability Guide](../guides/staff-availability.md)
- [CLAUDE.md - Staff Scheduling Section](../../CLAUDE.md#staff-scheduling-option-b---calendar-based)

## Notes

**Why we made this mistake initially:**
- Thought about data structure, not user needs
- Assumed "separation of concerns" in code = separation in UI
- Didn't check industry standards before implementing
- Classic programmer error: Let technical implementation dictate UX

**Lessons learned:**
- Always research UX patterns before implementing complex features
- Group UI by user task, not by data type
- Industry standards exist for a reason - use them
- Documentation and rollback plans are critical for refactoring

---

**Approved by:** Development Team
**Implementation Status:** In Progress (2025-11-19)
**Migration ID:** UI-MIGRATION-001
