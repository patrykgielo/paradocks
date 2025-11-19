# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 car detailing booking application with:
- **Backend:** Laravel 12, PHP 8.2+, MySQL 8.0
- **Frontend:** Vite 7+, Tailwind CSS 4.0
- **Admin Panel:** Filament v3.3+
- **Queue:** Redis with Laravel Horizon
- **Containerization:** Docker Compose (8 services)

**Local URL:** https://paradocks.local:8444

📚 **Complete Documentation:** [app/docs/README.md](app/docs/README.md)

⚠️ **CRITICAL:** Documentation is in `app/docs/`, NOT `/docs/` in repository root!

## Quick Start

```bash
# One-command setup
./docker-init.sh

# Add domain to hosts
sudo ./add-hosts-entry.sh

# Run required seeders (⚠️ CRITICAL - always after migrate:fresh)
docker compose exec app php artisan db:seed --class=VehicleTypeSeeder
docker compose exec app php artisan db:seed --class=RolePermissionSeeder
docker compose exec app php artisan db:seed --class=EmailTemplateSeeder
docker compose exec app php artisan db:seed --class=SmsTemplateSeeder
docker compose exec app php artisan db:seed --class=SettingSeeder

# Staff scheduling is now managed via new calendar-based system:
# - /admin/staff-schedules (base weekly patterns)
# - /admin/staff-date-exceptions (single-day overrides)
# - /admin/staff-vacation-periods (vacation management)
# Or via Employee edit page → Harmonogramy/Wyjątki/Urlopy tabs

# Create admin user
docker compose exec app php artisan make:filament-user

# Access application
https://paradocks.local:8444
https://paradocks.local:8444/admin
```

**See:** [Quick Start Guide](app/docs/guides/quick-start.md)

## Essential Commands

### Development

```bash
# Start all services (Laravel + Queue + Logs + Vite)
cd app && composer run dev

# Frontend dev server only
cd app && npm run dev

# Production build
cd app && npm run build
```

### Database

```bash
# Run migrations
docker compose exec app php artisan migrate

# Fresh migrations with seeding
docker compose exec app php artisan migrate:fresh --seed

# ⚠️ CRITICAL: migrate:fresh --seed only runs DatabaseSeeder!
# You MUST manually run these seeders afterward:
docker compose exec app php artisan db:seed --class=VehicleTypeSeeder
docker compose exec app php artisan db:seed --class=RolePermissionSeeder
docker compose exec app php artisan db:seed --class=EmailTemplateSeeder
docker compose exec app php artisan db:seed --class=SmsTemplateSeeder
docker compose exec app php artisan db:seed --class=SettingSeeder

# NOTE: Staff availability is managed via admin panel (/admin/service-availabilities)
# Use "Ustaw standardowy harmonogram" action to quickly set schedules for employees
```

**See:** [Commands Reference](app/docs/guides/commands.md)

## Architecture Overview

### Directory Structure

```
app/
├── app/              # Core application code
├── config/           # Configuration files
├── database/         # Migrations, seeders, factories
├── docs/             # ⚠️ Complete documentation (NOT /docs/ in root!)
│   ├── environments/ # Staging/production live docs
│   ├── deployment/   # Deployment guides & ADRs
│   ├── architecture/ # Database schema, models
│   ├── features/     # Feature-specific docs
│   ├── guides/       # How-to guides
│   └── decisions/    # Architecture Decision Records
├── resources/        # Blade views, CSS, JavaScript
├── routes/           # Route definitions
├── tests/            # PHPUnit tests
└── storage/          # Logs, cache, uploads
```

**⚠️ CRITICAL:** Documentation is in `app/docs/`, NOT `/docs/` in repository root!

### Key Technologies

- **Database:** MySQL 8.0 (Docker container: `paradocks-mysql`)
- **Queue:** Redis with Horizon dashboard (`/horizon`)
- **Email:** Queue-based with Gmail SMTP App Password
- **Maps:** Google Maps Places Autocomplete API (Modern JS API, NOT Web Components)
- **Permissions:** Spatie Laravel Permission
- **Styling:** Tailwind CSS 4.0 (⚠️ plugin order matters!)

**See:** [Database Schema](app/docs/architecture/database-schema.md)

## Docker Quick Reference

```bash
# Start all containers
docker compose up -d

# View logs
docker compose logs -f [service]

# Run artisan commands
docker compose exec app php artisan <command>

# Access MySQL shell
docker compose exec mysql mysql -u paradocks -ppassword paradocks

# Stop containers
docker compose down
```

**Services:** app (PHP-FPM), nginx (reverse proxy), mysql, node (Vite), redis, queue, horizon, scheduler

**URLs:**
- App: https://paradocks.local:8444
- Admin: https://paradocks.local:8444/admin
- Horizon: https://paradocks.local:8444/horizon
- Vite: http://paradocks.local:5173

**See:** [Docker Guide](app/docs/guides/docker.md)

## Filament Admin Panel

**URL:** https://paradocks.local:8444/admin
**Default Credentials:** admin@example.com / password

```bash
# Create new Filament resource
docker compose exec app php artisan make:filament-resource ModelName

# Create new admin user
docker compose exec app php artisan make:filament-user

# Optimize for production
docker compose exec app php artisan filament:optimize
```

**Access Control:** `app/Models/User.php` → `canAccessPanel()` method

## Configuration

### Critical Environment Variables

```bash
# Database (MySQL 8.0 in Docker)
DB_CONNECTION=mysql
DB_HOST=paradocks-mysql
DB_DATABASE=paradocks
DB_USERNAME=paradocks
DB_PASSWORD=password

# Email (Gmail SMTP with App Password)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password

# Queue (Redis)
QUEUE_CONNECTION=redis
REDIS_HOST=redis

# Google Maps
GOOGLE_MAPS_API_KEY=AIzaSy...
GOOGLE_MAPS_MAP_ID=your_map_id
```

## Feature Documentation

### Email System

Complete transactional email system with queue-based delivery (PL/EN templates).

- **Architecture:** EmailService, SmtpMailer, 4 database tables
- **Templates:** 18 templates (9 types × 2 languages)
- **Jobs:** Reminders (24h, 2h), Follow-ups, Daily digest
- **Admin:** Filament resources for templates, logs, events, suppressions

**See:** [Email System](app/docs/features/email-system/README.md)

### Vehicle Management

Capture vehicle information (type, brand, model, year) for service preparation.

- **Architecture:** 4 tables + pivot (vehicle_types, car_brands, car_models)
- **Filament:** 3 resources with CRUD, filters, bulk actions
- **API:** Vehicle data endpoints for booking wizard
- **Policy:** Customer declaration only (no DB constraints)

**See:** [Vehicle Management](app/docs/features/vehicle-management/README.md)

### Google Maps Integration

Places Autocomplete for accurate location capture in booking wizard.

- **API:** Modern JavaScript API (NOT Web Components!)
- **Database:** 5 location fields in appointments table
- **Captures:** Address, lat/lng, place_id, address_components
- **Map:** AdvancedMarkerElement with DROP animation

**See:** [Google Maps Integration](app/docs/features/google-maps/README.md)

### Settings System

Centralized settings management via Filament admin panel.

- **Groups:** booking, map, contact, marketing
- **Service:** SettingsManager singleton
- **Admin:** Custom Filament page at `/admin/system-settings`
- **⚠️ Gotcha:** Use `app()` helper, NOT constructor injection in Livewire

**See:** [Settings System](app/docs/features/settings-system/README.md)

### Booking System

Multi-step wizard for appointment booking with validation.

- **Steps:** Service → DateTime → Vehicle/Location → Review
- **Integration:** Vehicle management, Google Maps, settings
- **Validation:** Frontend (JS) + Backend (Laravel)
- **Status:** pending → confirmed → in_progress → completed/cancelled

**See:** [Booking System](app/docs/features/booking-system/README.md)

### Staff Scheduling (Option B - Calendar-based)

Complete calendar-based staff availability management system replacing the old day_of_week pattern.

- **Architecture:** Base Schedules + Date Exceptions + Vacation Periods
- **Database:** 4 tables (staff_schedules, staff_date_exceptions, staff_vacation_periods, service_staff pivot)
- **Service:** StaffScheduleService with priority logic (Vacation → Exception → Base Schedule)
- **Admin:** 3 Filament Resources + 4 RelationManagers in EmployeeResource
- **Features:**
  - Recurring weekly schedules with effective date ranges
  - Single-day exceptions (sick days, doctor visits, extra work days)
  - Vacation period management with approval workflow
  - Service-staff assignments via pivot table
  - Calendar-based availability checking (not just recurring weekdays)

**Admin URLs:**
- `/admin/staff-schedules` - Base weekly patterns (Mon-Fri 9-17)
- `/admin/staff-date-exceptions` - Single day overrides
- `/admin/staff-vacation-periods` - Vacation management
- `/admin/employees/{id}/edit` → Tabs: Usługi, Harmonogramy, Wyjątki, Urlopy

**Key Files:**
- `app/Services/StaffScheduleService.php` - Core availability logic
- `app/Services/AppointmentService.php` - Integrated with new system
- `app/Models/StaffSchedule.php` - Base patterns
- `app/Models/StaffDateException.php` - Day overrides
- `app/Models/StaffVacationPeriod.php` - Vacation ranges
- `database/migrations/2025_11_19_*` - All schema migrations

**Migration Notes:**
- Old `service_availabilities` data migrated automatically
- 40 redundant records deduplicated to separate schedules + service assignments
- Zero data loss, backward compatible
- Old ServiceAvailabilityResource still functional (legacy)

**Usage Example:**
1. Create base schedule: Jan works Mon-Fri 9:00-17:00
2. Add exception: Jan unavailable 2025-12-24 (Christmas Eve)
3. Add vacation: Jan on vacation 2025-07-01 to 2025-07-14
4. Assign services: Jan can perform "Detailing wewnętrzny" + "Korekta lakieru"
5. System checks availability: Vacation (blocks all) → Exception (overrides schedule) → Base schedule

**See:** [Staff Availability Guide](app/docs/guides/staff-availability.md)

## Production Build

**IMPORTANT:** This project uses Tailwind CSS 4.0 with `@tailwindcss/vite` plugin.

```bash
# Build production assets
cd app && npm run build

# Verify output
ls -la app/public/build/
cat app/public/build/.vite/manifest.json
```

**Expected Output:**
- `public/build/assets/app-[hash].css` - Minified CSS
- `public/build/assets/app-[hash].js` - Minified JavaScript
- `public/build/.vite/manifest.json` - Asset manifest (Vite 7+)

**Critical Configuration:**

vite.config.js:
```javascript
plugins: [
    tailwindcss(), // ⚠️ MUST be BEFORE laravel() for v4.0
    laravel({ ... }),
]
```

resources/css/app.css:
```css
@import 'tailwindcss';  /* NOT @tailwind directives */
@source '../**/*.blade.php';
```

**See:** [Production Build Guide](app/docs/guides/production-build.md)

## User Model Pattern

**Important:** User model has `first_name` and `last_name` fields, NOT `name` column.

```php
$user->name        // Returns "Jan Kowalski" (via accessor)
$user->first_name  // Returns "Jan"
$user->last_name   // Returns "Kowalski"
```

**Why?** Email templates, Blade views, and third-party packages expect `$user->name`.

**See:** [User Model Documentation](app/docs/architecture/user-model.md)

## Documentation

📚 **Start here:** [app/docs/README.md](app/docs/README.md)

**⚠️ IMPORTANT:** All documentation is in `app/docs/` directory, NOT `/docs/` in repository root!

**Key Documentation:**
- [Project Map](app/docs/project_map.md) - System topology, modules, key files
- [Staging Server Docs](app/docs/environments/staging/) - Live staging documentation
- [Deployment ADRs](app/docs/deployment/) - Infrastructure decisions (ADR-007 to 009)
- [Quick Start](app/docs/guides/quick-start.md) - Complete setup guide
- [Commands](app/docs/guides/commands.md) - All available commands
- [Docker](app/docs/guides/docker.md) - Container architecture
- [Troubleshooting](app/docs/guides/troubleshooting.md) - Common issues
- [Database Schema](app/docs/architecture/database-schema.md) - Complete DB structure
- [Architecture Decisions](app/docs/decisions/) - Application ADRs

**Polish Note:** Zawsze sprawdzaj `@app/docs/` przed skanowaniem projektu, żeby nie tracić tokenów na kolejną analizę. Dokumentacja jest TYLKO w `app/docs/`, NIE w root `/docs/`. Zawsze aktualizuj dokumentację po implementacji nowych rzeczy.

## Troubleshooting

### Build Issues

```bash
# CSS not loading
cd app && npm run build
docker compose exec app php artisan optimize:clear

# Vite cache issues
rm -rf app/node_modules/.vite
cd app && npm ci
```

### Database Issues

```bash
# Connection refused
docker compose restart mysql
# Verify .env: DB_HOST=paradocks-mysql (NOT localhost!)

# Migration fails
docker compose exec app php artisan migrate:fresh --seed
# Then run required seeders (see Quick Start)
```

### Queue Issues

```bash
# Emails not sending
docker compose restart queue horizon
docker compose exec app php artisan queue:retry all

# Check failed jobs
https://paradocks.local:8444/horizon/failed
```

### OPcache / Code Changes Not Applying

**Problem:** Code changes don't take effect, old code still executes (especially Filament Resources).

**Cause:** PHP OPcache in Docker containers caches bytecode. `php artisan optimize:clear` only clears **CLI** OPcache, not **PHP-FPM workers** (web server).

```bash
# Solution: Restart containers to clear PHP-FPM OPcache
docker compose restart app horizon queue scheduler

# Verify containers restarted (check "STATUS" for recent timestamp)
docker compose ps

# Then clear Laravel caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan filament:optimize-clear
```

**When to restart containers:**
- After changing Filament Resources, Livewire components, or Actions
- After Composer updates (composer.json changes)
- When code changes don't appear in browser
- After modifying .env file

**See:** [Troubleshooting Guide](app/docs/guides/troubleshooting.md)

## Testing

```bash
# Run all tests
cd app && composer run test

# Run specific suite
cd app && php artisan test --testsuite=Feature

# Code formatting
cd app && ./vendor/bin/pint
```

## Getting Help

1. **Check documentation:** [docs/README.md](app/docs/README.md)
2. **Search feature docs:** [docs/features/](app/docs/features/)
3. **Check logs:** `docker compose logs -f` or `storage/logs/laravel.log`
4. **Enable debug mode:** Set `APP_DEBUG=true` in `.env` (development only)
