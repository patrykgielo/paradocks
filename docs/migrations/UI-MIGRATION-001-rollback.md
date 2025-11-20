# UI-MIGRATION-001: Rollback Guide

**Migration:** Staff Scheduling Menu Consolidation (4→2)
**Risk Level:** 🟢 LOW (all changes reversible)
**Recovery Time Objective (RTO):** 2-5 minutes
**Data Loss Risk:** 🟢 ZERO (no database changes to core tables)

---

## Quick Reference

| Scenario | Time | Complexity | Data Loss Risk |
|----------|------|------------|----------------|
| **Restore old menu (hide new)** | 2 min | 🟢 Easy | None |
| **Complete code rollback** | 5 min | 🟡 Medium | None |
| **Database rollback** | N/A | N/A | N/A (not needed) |

---

## SCENARIO 1: Quick Rollback (2 minutes)

**Use when:** New unified view doesn't work, need to restore old 4-menu structure immediately

### Step 1: Restore Navigation Visibility

Edit **3 Resource files** and change `false` → `true`:

**File 1:** `app/Filament/Resources/StaffScheduleResource.php`
```php
// Line ~22-24 (approximate)
// Change FROM:
protected static bool $shouldRegisterNavigation = false;

// Change TO:
protected static bool $shouldRegisterNavigation = true;
```

**File 2:** `app/Filament/Resources/StaffDateExceptionResource.php`
```php
// Line ~22-24 (approximate)
// Change FROM:
protected static bool $shouldRegisterNavigation = false;

// Change TO:
protected static bool $shouldRegisterNavigation = true;
```

**File 3:** `app/Filament/Resources/ServiceAvailabilityResource.php`
```php
// Line ~22-24 (approximate)
// Change FROM:
protected static bool $shouldRegisterNavigation = false;

// Change TO:
protected static bool $shouldRegisterNavigation = true;
```

### Step 2: (Optional) Hide New Unified View

**File:** `app/Filament/Pages/StaffScheduleCalendar.php`
```php
// Add this property:
protected static bool $shouldRegisterNavigation = false;
```

### Step 3: Clear Caches & Restart

```bash
# Navigate to app directory
cd /var/www/projects/paradocks/app

# Clear Filament cache
docker compose exec app php artisan filament:optimize-clear

# Clear config cache
docker compose exec app php artisan config:clear

# Restart containers to clear OPcache
docker compose restart app horizon queue scheduler
```

### Step 4: Verify Rollback

```bash
# Check containers restarted
docker compose ps

# Expected: app, horizon, queue, scheduler show "Up X seconds"

# Test old URLs are accessible
curl -I https://paradocks.local:8444/admin/staff-schedules
# Expected: HTTP 200 OK

curl -I https://paradocks.local:8444/admin/staff-date-exceptions
# Expected: HTTP 200 OK

curl -I https://paradocks.local:8444/admin/service-availabilities
# Expected: HTTP 200 OK
```

### Result

✅ Menu restored to 4 items:
- Harmonogramy Bazowe
- Wyjątki Od Harmonogramu
- Dostępności Pracowników
- Urlopy

✅ New unified view hidden (if Step 2 applied)

✅ All data intact (zero changes to database)

---

## SCENARIO 2: Complete Code Rollback (5 minutes)

**Use when:** Need to completely remove new code and restore to pre-migration state

### Step 1: Git Rollback to Pre-Migration

```bash
# Navigate to app root
cd /var/www/projects/paradocks/app

# Check git log to find migration commit
git log --oneline --grep="UI-MIGRATION-001" -n 5

# Example output:
# a1b2c3d UI-MIGRATION-001: Consolidate staff scheduling menu (4→2 items)
# e4f5g6h Previous commit before migration

# Restore files from previous commit (before migration)
git checkout HEAD~1 -- app/Filament/Resources/StaffScheduleResource.php
git checkout HEAD~1 -- app/Filament/Resources/StaffDateExceptionResource.php
git checkout HEAD~1 -- app/Filament/Resources/ServiceAvailabilityResource.php
git checkout HEAD~1 -- app/Filament/Resources/StaffVacationPeriodResource.php
```

### Step 2: Delete New Files (Optional)

```bash
# Remove new unified view page
rm app/Filament/Pages/StaffScheduleCalendar.php

# Remove migration tracker service (optional, doesn't hurt to keep)
rm app/Services/MigrationTrackerService.php

# Remove documentation (optional, good to keep for history)
# rm app/docs/decisions/ADR-011-staff-scheduling-ui-consolidation.md
# rm app/docs/migrations/UI-MIGRATION-001-staff-scheduling.md
# rm app/docs/migrations/UI-MIGRATION-001-rollback.md
```

### Step 3: Clear Caches & Restart

```bash
# Clear all Laravel caches
docker compose exec app php artisan optimize:clear

# Clear Filament cache
docker compose exec app php artisan filament:optimize-clear

# Restart containers
docker compose restart app horizon queue scheduler
```

### Step 4: Verify Complete Rollback

```bash
# Check menu structure (visit in browser)
# https://paradocks.local:8444/admin

# Verify 4 menu items under "Harmonogramy":
# - Harmonogramy Bazowe
# - Wyjątki Od Harmonogramu
# - Dostępności Pracowników
# - Urlopy

# Check booking still works
curl -X POST https://paradocks.local:8444/booking/available-slots \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: $(grep 'csrf-token' <<< $(curl -s https://paradocks.local:8444/booking) | sed 's/.*content="\([^"]*\)".*/\1/')" \
  -d '{"service_id": 1, "date": "2025-12-10"}'

# Expected: JSON response with slots array
```

### Step 5: (Optional) Rollback Git Commit

```bash
# If you want to completely undo the migration commit
git revert <commit-hash>

# Example:
git revert a1b2c3d
git push origin main
```

### Result

✅ All new code removed
✅ Old Resources fully restored
✅ Menu shows 4 items again
✅ System working as before migration

---

## SCENARIO 3: Database Rollback

**Answer:** **NOT NEEDED**

**Reason:**
This migration makes **ZERO changes** to core database tables:

✅ **No tables dropped**
✅ **No columns added/removed**
✅ **No data modified**
✅ **No schema changes**

**Only new table:** `ui_migrations` (tracking only, can be safely ignored or dropped)

### If You Want to Drop Tracking Table (Optional)

```bash
# Create rollback migration
docker compose exec app php artisan make:migration drop_ui_migrations_table

# Edit migration file:
# database/migrations/YYYY_MM_DD_HHMMSS_drop_ui_migrations_table.php

# Add:
# public function up() {
#     Schema::dropIfExists('ui_migrations');
# }
#
# public function down() {
#     // Restore table if needed (copy from create migration)
# }

# Run migration
docker compose exec app php artisan migrate
```

**Impact:** None on functionality. Tracking table is for audit trail only.

---

## DATA VERIFICATION AFTER ROLLBACK

### Check Core Tables (Should be unchanged)

```bash
# Connect to MySQL
docker compose exec mysql mysql -u paradocks -ppassword paradocks

# Run verification queries
SELECT COUNT(*) FROM staff_schedules;
-- Expected: 10 (unchanged)

SELECT COUNT(*) FROM staff_date_exceptions;
-- Expected: 0 or X (whatever was there before)

SELECT COUNT(*) FROM staff_vacation_periods;
-- Expected: 0 or X (whatever was there before)

SELECT COUNT(*) FROM service_staff;
-- Expected: 16 (unchanged)

SELECT COUNT(*) FROM service_availabilities;
-- Expected: 0 (legacy table)

# Check sample data
SELECT * FROM staff_schedules LIMIT 5;
-- Verify records look correct
```

### Check Application Logs

```bash
# Check for errors after rollback
docker compose exec app tail -n 100 storage/logs/laravel.log | grep -i error

# Expected: No new errors related to scheduling

# Check Filament logs
docker compose exec app tail -n 50 storage/logs/laravel.log | grep -i filament

# Expected: No navigation or resource loading errors
```

---

## TROUBLESHOOTING

### Problem: Menu still shows new unified view after Step 1

**Cause:** OPcache not cleared or containers not restarted

**Solution:**
```bash
docker compose restart app
docker compose exec app php artisan filament:optimize-clear
docker compose exec app php artisan config:clear
```

### Problem: Old Resources show 404 after rollback

**Cause:** Routes not registered or Filament cache issue

**Solution:**
```bash
# Clear all caches
docker compose exec app php artisan optimize:clear

# Rebuild Filament discovery
docker compose exec app php artisan filament:optimize

# Restart
docker compose restart app
```

### Problem: Booking calendar shows 500 error

**Cause:** Unlikely related to this migration (no backend changes)

**Check:**
```bash
# View detailed error
docker compose exec app tail -f storage/logs/laravel.log

# Common causes (unrelated to migration):
# - slotIntervalMinutes() missing (already fixed earlier)
# - Database connection issue
# - StaffScheduleService error
```

**Note:** This migration doesn't change booking logic, so 500 errors are likely pre-existing or unrelated.

### Problem: Git rollback shows conflicts

**Cause:** Files modified after migration

**Solution:**
```bash
# Force checkout (CAREFUL - loses local changes)
git checkout HEAD~1 -- app/Filament/Resources/*.php --force

# OR manually restore from git
git show HEAD~1:app/Filament/Resources/StaffScheduleResource.php > app/Filament/Resources/StaffScheduleResource.php
```

---

## VERIFICATION CHECKLIST

After completing rollback, verify:

### UI Verification
- [ ] Admin panel loads without errors
- [ ] Menu shows 4 items under "Harmonogramy"
- [ ] All 4 old Resources accessible:
  - [ ] `/admin/staff-schedules`
  - [ ] `/admin/staff-date-exceptions`
  - [ ] `/admin/service-availabilities`
  - [ ] `/admin/staff-vacation-periods`
- [ ] Employee edit page RelationManagers work
- [ ] No 404 errors in navigation

### Functional Verification
- [ ] Can create new base schedule
- [ ] Can create new exception (if applicable)
- [ ] Can create new vacation period
- [ ] Booking wizard loads calendar
- [ ] Booking wizard fetches available slots
- [ ] Can complete test appointment booking

### Data Integrity Verification
- [ ] All 10 base schedules visible
- [ ] Service-staff assignments intact (16 records)
- [ ] No data loss in any table
- [ ] Database query counts normal (no N+1 issues)

### Log Verification
- [ ] No errors in `storage/logs/laravel.log`
- [ ] No errors in browser console
- [ ] Docker containers healthy (`docker compose ps`)

---

## ESCALATION

If rollback fails or data integrity issues found:

### Immediate Actions

1. **Stop deployment**
   ```bash
   # Prevent further changes
   docker compose down
   ```

2. **Restore from backup** (if available)
   ```bash
   # Database backup (if taken before migration)
   docker compose exec mysql mysql -u paradocks -ppassword paradocks < backup_pre_migration.sql
   ```

3. **Contact development team**
   - Provide Laravel logs: `storage/logs/laravel.log`
   - Provide git status: `git status`
   - Provide database state: `SHOW TABLES; SELECT COUNT(*) FROM staff_schedules;`

### Recovery Resources

- **Git History:** `git log --oneline -20`
- **Migration Logs:** `app/docs/migrations/UI-MIGRATION-001-staff-scheduling.md`
- **ADR Document:** `app/docs/decisions/ADR-011-staff-scheduling-ui-consolidation.md`
- **Laravel Logs:** `storage/logs/laravel.log`

---

## POST-ROLLBACK ACTIONS

### 1. Update Migration Status (Optional)

```bash
# Mark migration as rolled back in database (if table exists)
docker compose exec mysql mysql -u paradocks -ppassword -e "
UPDATE paradocks.ui_migrations
SET status = 'rolled_back',
    rollback_reason = 'User requested rollback',
    rolled_back_at = NOW()
WHERE name = 'UI-MIGRATION-001';
"
```

### 2. Document Rollback Reason

Edit `app/docs/migrations/UI-MIGRATION-001-staff-scheduling.md`:

```markdown
## DEPLOYMENT HISTORY

| Date | Action | Status | Notes |
|------|--------|--------|-------|
| 2025-11-19 | Implementation | ❌ Rolled Back | Reason: [specify reason] |
```

### 3. Notify Team

- Update issue tracker / project board
- Notify QA/Admin that old menu is restored
- Schedule post-mortem if issues occurred

---

## PREVENTION FOR FUTURE MIGRATIONS

**Lessons from this rollback:**

1. ✅ **Always take git snapshot before migration**
   ```bash
   git tag migration-001-before
   ```

2. ✅ **Take database backup before migration**
   ```bash
   docker compose exec mysql mysqldump paradocks > backup.sql
   ```

3. ✅ **Test in staging first**
   - Deploy to staging environment
   - Test for 1-2 days
   - Then deploy to production

4. ✅ **Gradual rollout**
   - Phase 1: Add new view (keep old)
   - Phase 2: Hide old (after testing)
   - Phase 3: Remove old (after 30 days)

5. ✅ **Monitor metrics**
   - Error rates in Laravel logs
   - User feedback
   - Performance metrics

---

## CONTACT & SUPPORT

**Migration Documentation:**
- Main: `app/docs/migrations/UI-MIGRATION-001-staff-scheduling.md`
- ADR: `app/docs/decisions/ADR-011-staff-scheduling-ui-consolidation.md`
- This guide: `app/docs/migrations/UI-MIGRATION-001-rollback.md`

**Technical Support:**
- Check Laravel logs: `docker compose exec app tail -f storage/logs/laravel.log`
- Check application: https://paradocks.local:8444/admin
- Check database: `docker compose exec mysql mysql -u paradocks -ppassword paradocks`

---

**Migration ID:** UI-MIGRATION-001
**Rollback Guide Version:** 1.0
**Last Updated:** 2025-11-19
