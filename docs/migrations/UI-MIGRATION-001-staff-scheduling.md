# UI-MIGRATION-001: Staff Scheduling Menu Consolidation

**Type:** UI Refactoring (Non-Breaking)
**Date:** 2025-11-19
**Status:** In Progress
**ADR:** [ADR-011: Staff Scheduling UI Consolidation](../decisions/ADR-011-staff-scheduling-ui-consolidation.md)
**Rollback Guide:** [UI-MIGRATION-001 Rollback](./UI-MIGRATION-001-rollback.md)

## Summary

Consolidate staff scheduling interface from **4 separate menu items** to **2 unified views**, aligning with industry standards (Deputy, When I Work, Homebase).

**Change:** 4 menu items → 2 menu items
**Risk Level:** 🟢 LOW (additive changes, old Resources remain functional)
**Database Impact:** 🟢 ZERO (only adds tracking table)
**Rollback Time:** 2 minutes

---

## BEFORE STATE (Snapshot)

### Menu Structure

```
Navigation Group: "Harmonogramy"
├── Harmonogramy Bazowe
│   └── URL: /admin/staff-schedules
│   └── Resource: StaffScheduleResource
│   └── Records: 10
│
├── Wyjątki Od Harmonogramu
│   └── URL: /admin/staff-date-exceptions
│   └── Resource: StaffDateExceptionResource
│   └── Records: 0
│
├── Dostępności Pracowników (LEGACY)
│   └── URL: /admin/service-availabilities
│   └── Resource: ServiceAvailabilityResource
│   └── Records: 0 (migrated to new system)
│   └── Badge: "0" (showing empty state)
│
└── Urlopy
    └── URL: /admin/staff-vacation-periods
    └── Resource: StaffVacationPeriodResource
    └── Records: 0
```

### Files Inventory (BEFORE)

```
app/Filament/Resources/
├── StaffScheduleResource.php
│   ├── Status: Active, in navigation
│   ├── Lines: 217
│   └── Features: CRUD for base weekly schedules
│
├── StaffScheduleResource/Pages/
│   ├── ListStaffSchedules.php
│   ├── CreateStaffSchedule.php
│   └── EditStaffSchedule.php
│
├── StaffDateExceptionResource.php
│   ├── Status: Active, in navigation
│   ├── Lines: 205
│   └── Features: CRUD for single-day exceptions
│
├── StaffDateExceptionResource/Pages/
│   ├── ListStaffDateExceptions.php
│   ├── CreateStaffDateException.php
│   └── EditStaffDateException.php
│
├── ServiceAvailabilityResource.php
│   ├── Status: Active, in navigation (LEGACY)
│   ├── Lines: ~180
│   ├── Records: 0
│   └── Note: Old system before Option B migration
│
├── StaffVacationPeriodResource.php
│   ├── Status: Active, in navigation
│   ├── Lines: 259
│   └── Features: Vacation management + approval workflow
│
└── StaffVacationPeriodResource/Pages/
    ├── ListStaffVacationPeriods.php
    ├── CreateStaffVacationPeriod.php
    └── EditStaffVacationPeriod.php

app/Filament/Resources/EmployeeResource/RelationManagers/
├── StaffSchedulesRelationManager.php (active, used in Employee edit)
├── DateExceptionsRelationManager.php (active, used in Employee edit)
├── VacationPeriodsRelationManager.php (active, used in Employee edit)
└── ServicesRelationManager.php (active, used in Employee edit)
```

### Database State (BEFORE)

```sql
-- Core Tables (remain unchanged)
staff_schedules
├── Records: 10
├── Purpose: Base weekly patterns (Mon-Fri 9-17)
└── Schema: user_id, day_of_week, start_time, end_time, effective_from, effective_until, is_active

staff_date_exceptions
├── Records: 0
├── Purpose: Single-day overrides (sick days, extra work days)
└── Schema: user_id, exception_date, exception_type, start_time, end_time, reason

staff_vacation_periods
├── Records: 0
├── Purpose: Multi-day vacation ranges
└── Schema: user_id, start_date, end_date, reason, is_approved

service_staff
├── Records: 16
├── Purpose: Many-to-many pivot (services ↔ staff)
└── Schema: service_id, user_id

service_availabilities (LEGACY)
├── Records: 0
├── Purpose: Old system (pre-Option B)
└── Status: Migrated to new tables, kept for reference
```

### Active URLs (BEFORE)

| URL | Resource | Visible in Menu | Functional |
|-----|----------|----------------|------------|
| `/admin/staff-schedules` | StaffScheduleResource | ✅ Yes | ✅ Yes |
| `/admin/staff-date-exceptions` | StaffDateExceptionResource | ✅ Yes | ✅ Yes |
| `/admin/service-availabilities` | ServiceAvailabilityResource | ✅ Yes (badge: 0) | ✅ Yes |
| `/admin/staff-vacation-periods` | StaffVacationPeriodResource | ✅ Yes | ✅ Yes |

---

## AFTER STATE (Target)

### Menu Structure

```
Navigation Group: "Harmonogramy"
├── Harmonogramy ← NEW unified view
│   └── URL: /admin/staff-schedule-calendar
│   └── Page: StaffScheduleCalendar
│   └── Shows: Base schedules + Exceptions + Vacations (merged)
│   └── Color coding: Blue (base), Orange (exception), Green (vacation)
│
└── Wnioski o Czas Wolny ← Renamed
    └── URL: /admin/staff-vacation-periods (unchanged)
    └── Resource: StaffVacationPeriodResource (renamed label)
    └── Purpose: Approval workflow + history
```

### Files Inventory (AFTER)

```
app/Filament/Resources/
├── StaffScheduleResource.php
│   ├── Status: Hidden from navigation ($shouldRegisterNavigation = false)
│   ├── Lines: 217 (unchanged)
│   ├── Accessible: Via direct URL + RelationManagers
│   └── Purpose: Backup for rollback + RelationManager use
│
├── StaffDateExceptionResource.php
│   ├── Status: Hidden from navigation ($shouldRegisterNavigation = false)
│   ├── Lines: 205 (unchanged)
│   ├── Accessible: Via direct URL + RelationManagers
│   └── Purpose: Backup for rollback + RelationManager use
│
├── ServiceAvailabilityResource.php
│   ├── Status: Hidden from navigation ($shouldRegisterNavigation = false)
│   ├── Lines: ~180 (unchanged)
│   ├── Records: 0
│   └── Purpose: Legacy backup
│
└── StaffVacationPeriodResource.php
    ├── Status: Active, in navigation
    ├── Lines: 259 (mostly unchanged)
    ├── Modified: navigationLabel = 'Wnioski o Czas Wolny'
    └── Purpose: Vacation approval workflow

app/Filament/Pages/
└── StaffScheduleCalendar.php ← NEW FILE
    ├── Lines: ~150
    ├── Purpose: Unified calendar view
    ├── Query: Merges staff_schedules + staff_date_exceptions + staff_vacation_periods
    ├── Display: Table with color coding
    └── Features: Filters, date range selection

app/Services/
└── MigrationTrackerService.php ← NEW FILE
    ├── Lines: ~50
    ├── Purpose: Track UI migrations in database
    └── Methods: recordMigration(), recordRollback()

app/Filament/Resources/EmployeeResource/RelationManagers/
├── StaffSchedulesRelationManager.php (unchanged, still works)
├── DateExceptionsRelationManager.php (unchanged, still works)
├── VacationPeriodsRelationManager.php (unchanged, still works)
└── ServicesRelationManager.php (unchanged, still works)
```

### Database State (AFTER)

```sql
-- Core Tables (UNCHANGED)
staff_schedules (10 records) ← NO CHANGES
staff_date_exceptions (0 records) ← NO CHANGES
staff_vacation_periods (0 records) ← NO CHANGES
service_staff (16 records) ← NO CHANGES
service_availabilities (0 records) ← NO CHANGES (legacy)

-- New Table (tracking only)
ui_migrations ← NEW TABLE
├── Records: 1 (this migration)
├── Purpose: Track UI refactorings (like database migrations)
└── Schema: name, type, details (JSON), status, executed_at, rolled_back_at
```

### Active URLs (AFTER)

| URL | Resource/Page | Visible in Menu | Functional | Notes |
|-----|--------------|----------------|------------|-------|
| `/admin/staff-schedule-calendar` | StaffScheduleCalendar | ✅ Yes (NEW) | ✅ Yes | Unified view |
| `/admin/staff-vacation-periods` | StaffVacationPeriodResource | ✅ Yes | ✅ Yes | Renamed label |
| `/admin/staff-schedules` | StaffScheduleResource | ❌ No | ✅ Yes | Direct access works |
| `/admin/staff-date-exceptions` | StaffDateExceptionResource | ❌ No | ✅ Yes | Direct access works |
| `/admin/service-availabilities` | ServiceAvailabilityResource | ❌ No | ✅ Yes | Direct access works |

---

## CHANGES MANIFEST

### Added Files

| File | Purpose | Lines | Risk |
|------|---------|-------|------|
| `app/Filament/Pages/StaffScheduleCalendar.php` | Unified calendar view | ~150 | 🟢 Low (new feature) |
| `app/Services/MigrationTrackerService.php` | UI migration tracking | ~50 | 🟢 Low (logging only) |
| `database/migrations/xxx_create_ui_migrations_table.php` | Tracking table | ~30 | 🟢 Low (new table) |
| `app/docs/decisions/ADR-011-staff-scheduling-ui-consolidation.md` | Architecture decision | Doc | 🟢 None |
| `app/docs/migrations/UI-MIGRATION-001-staff-scheduling.md` | This file | Doc | 🟢 None |
| `app/docs/migrations/UI-MIGRATION-001-rollback.md` | Rollback guide | Doc | 🟢 None |

**Total New Code:** ~230 lines

### Modified Files

| File | What Changed | Lines Changed | Risk |
|------|--------------|---------------|------|
| `StaffScheduleResource.php` | `$shouldRegisterNavigation = false` | 1 | 🟢 Low (reversible) |
| `StaffDateExceptionResource.php` | `$shouldRegisterNavigation = false` | 1 | 🟢 Low (reversible) |
| `ServiceAvailabilityResource.php` | `$shouldRegisterNavigation = false` | 1 | 🟢 Low (reversible) |
| `StaffVacationPeriodResource.php` | `$navigationLabel = 'Wnioski o Czas Wolny'` | 1 | 🟢 Low (cosmetic) |
| `app/CLAUDE.md` | Added "UI/Feature Migrations" section | ~30 | 🟢 None (docs) |

**Total Modified Code:** 4 lines

### Deleted Files

| File | Reason | Risk |
|------|--------|------|
| **None** | Safe approach: hide, don't delete | 🟢 None |

### Database Migrations

| Migration | Purpose | Risk | Rollback |
|-----------|---------|------|----------|
| `xxx_create_ui_migrations_table.php` | Tracking table for UI changes | 🟢 Low | Drop table |

**NO changes to existing tables:**
- ✅ staff_schedules (unchanged)
- ✅ staff_date_exceptions (unchanged)
- ✅ staff_vacation_periods (unchanged)
- ✅ service_staff (unchanged)
- ✅ service_availabilities (unchanged)

---

## IMPLEMENTATION STEPS

### Phase 1: Documentation ✅

- [x] Create ADR-011
- [x] Create UI-MIGRATION-001 (this file)
- [ ] Create UI-MIGRATION-001-rollback.md

### Phase 2: Tracking Infrastructure

- [ ] Create MigrationTrackerService
- [ ] Create ui_migrations table migration
- [ ] Run migration: `php artisan migrate`

### Phase 3: Implementation

- [ ] Create StaffScheduleCalendar page
- [ ] Hide old Resources from navigation (3 files)
- [ ] Rename vacation resource label
- [ ] Add migration tracking in Calendar page

### Phase 4: Finalization

- [ ] Update CLAUDE.md
- [ ] Git commit with detailed message
- [ ] Test all functionality
- [ ] Mark migration as completed in ui_migrations table

---

## TESTING CHECKLIST

### Functional Tests

- [ ] New unified view displays data from all 3 tables
- [ ] Color coding works (blue=base, orange=exception, green=vacation)
- [ ] Filters work (show/hide different event types)
- [ ] Old Resources accessible via direct URL
- [ ] RelationManagers in Employee edit still work
- [ ] Booking wizard fetches slots without errors
- [ ] No 500 errors in Laravel logs

### UX Tests

- [ ] Admin can navigate new unified view intuitively
- [ ] Menu has only 2 items (not 4)
- [ ] "Wnioski o Czas Wolny" label is clear
- [ ] No broken links in navigation

### Data Integrity Tests

- [ ] All 10 base schedule records visible
- [ ] Can create new exceptions (if 0 records)
- [ ] Can create new vacations (if 0 records)
- [ ] Service-staff assignments unchanged (16 records)

### Performance Tests

- [ ] Unified view loads in <2 seconds
- [ ] No N+1 query issues
- [ ] Database query count reasonable

---

## ROLLBACK INSTRUCTIONS

**Quick Rollback (2 minutes):**
See: [UI-MIGRATION-001 Rollback Guide](./UI-MIGRATION-001-rollback.md)

```bash
# Short version:
# 1. Change $shouldRegisterNavigation = true in 3 Resources
# 2. docker compose restart app
# 3. php artisan filament:optimize-clear
```

**Database Rollback:** NOT NEEDED (only tracking table added)

---

## DEPLOYMENT HISTORY

| Date | Action | Status | Executor | Notes |
|------|--------|--------|----------|-------|
| 2025-11-19 | Created migration docs | ✅ Done | Claude Code | ADR-011 + docs |
| 2025-11-19 | Implementation start | 🔄 In Progress | Development Team | Current |
| TBD | Testing period (3 days) | ⏳ Pending | QA/Admin | Monitor logs |
| TBD | Mark as stable | ⏳ Pending | Development Team | After 30 days |

---

## METRICS & MONITORING

### Success Criteria

**After 3 days:**
- [ ] Zero 500 errors related to scheduling
- [ ] Admin feedback positive
- [ ] All booking appointments created successfully
- [ ] No rollback requests

**After 30 days:**
- [ ] Admin prefers new unified view
- [ ] No usage of old hidden Resources (check access logs)
- [ ] Can consider permanent removal of old Resources (optional)

### Monitoring Queries

```sql
-- Check migration status
SELECT * FROM ui_migrations WHERE name = 'UI-MIGRATION-001';

-- Check access to old URLs (if logging enabled)
-- SELECT * FROM access_logs WHERE url LIKE '%staff-schedules%' AND created_at > '2025-11-19';

-- Verify data integrity
SELECT COUNT(*) FROM staff_schedules; -- Should be 10
SELECT COUNT(*) FROM service_staff; -- Should be 16
```

---

## RELATED DOCUMENTATION

- [ADR-011: Staff Scheduling UI Consolidation](../decisions/ADR-011-staff-scheduling-ui-consolidation.md)
- [UI-MIGRATION-001 Rollback Guide](./UI-MIGRATION-001-rollback.md)
- [Staff Availability Guide](../guides/staff-availability.md)
- [CLAUDE.md - Staff Scheduling](../../CLAUDE.md#staff-scheduling-option-b---calendar-based)

---

## NOTES

### Why This Migration Was Necessary

**Original Problem:**
- Data-centric design (3 tables → 3 menu items)
- Ignored industry UX standards
- Fragmented user workflow

**Root Cause:**
Programmer error - let database structure dictate UI structure

**Lesson Learned:**
Always research UX patterns before implementing. Group UI by user task, not data type.

### Future Improvements (Optional)

**V2 Enhancements (post-migration):**
- [ ] Replace table view with full calendar component (FullCalendar.js or TOAST UI)
- [ ] Add drag-and-drop for exceptions
- [ ] Inline editing (click to modify)
- [ ] Week/month/day view toggles
- [ ] Export to PDF/Excel

**V3 Enhancements (long-term):**
- [ ] Mobile app view
- [ ] Employee self-service (request time-off from calendar)
- [ ] Conflict detection UI
- [ ] Automated schedule suggestions

---

**Migration ID:** UI-MIGRATION-001
**Status:** In Progress
**Last Updated:** 2025-11-19
