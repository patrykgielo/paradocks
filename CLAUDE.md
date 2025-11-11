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

📚 **Complete Documentation:** [docs/README.md](docs/README.md)

## Quick Start

```bash
# One-command setup
./docker-init.sh

# Add domain to hosts
sudo ./add-hosts-entry.sh

# Run required seeders (⚠️ CRITICAL - always after migrate:fresh)
docker compose exec app php artisan db:seed --class=VehicleTypeSeeder
docker compose exec app php artisan db:seed --class=RolePermissionSeeder
docker compose exec app php artisan db:seed --class=ServiceAvailabilitySeeder
docker compose exec app php artisan db:seed --class=EmailTemplateSeeder
docker compose exec app php artisan db:seed --class=SettingSeeder

# Create admin user
docker compose exec app php artisan make:filament-user

# Access application
https://paradocks.local:8444
https://paradocks.local:8444/admin
```

**See:** [Quick Start Guide](docs/guides/quick-start.md)

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
docker compose exec app php artisan db:seed --class=ServiceAvailabilitySeeder
docker compose exec app php artisan db:seed --class=EmailTemplateSeeder
docker compose exec app php artisan db:seed --class=SettingSeeder
```

**See:** [Commands Reference](docs/guides/commands.md)

## Architecture Overview

### Directory Structure

```
app/
├── app/              # Core application code
├── config/           # Configuration files
├── database/         # Migrations, seeders, factories
├── resources/        # Blade views, CSS, JavaScript
├── routes/           # Route definitions
├── tests/            # PHPUnit tests
└── storage/          # Logs, cache, uploads

docs/                 # Complete documentation
├── architecture/     # Database schema, models
├── features/         # Feature-specific docs
├── guides/           # How-to guides
└── decisions/        # Architecture Decision Records
```

### Key Technologies

- **Database:** MySQL 8.0 (Docker container: `paradocks-mysql`)
- **Queue:** Redis with Horizon dashboard (`/horizon`)
- **Email:** Queue-based with Gmail SMTP App Password
- **Maps:** Google Maps Places Autocomplete API (Modern JS API, NOT Web Components)
- **Permissions:** Spatie Laravel Permission
- **Styling:** Tailwind CSS 4.0 (⚠️ plugin order matters!)

**See:** [Database Schema](docs/architecture/database-schema.md)

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

**See:** [Docker Guide](docs/guides/docker.md)

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

**See:** [Email System](docs/features/email-system/README.md)

### Vehicle Management

Capture vehicle information (type, brand, model, year) for service preparation.

- **Architecture:** 4 tables + pivot (vehicle_types, car_brands, car_models)
- **Filament:** 3 resources with CRUD, filters, bulk actions
- **API:** Vehicle data endpoints for booking wizard
- **Policy:** Customer declaration only (no DB constraints)

**See:** [Vehicle Management](docs/features/vehicle-management/README.md)

### Google Maps Integration

Places Autocomplete for accurate location capture in booking wizard.

- **API:** Modern JavaScript API (NOT Web Components!)
- **Database:** 5 location fields in appointments table
- **Captures:** Address, lat/lng, place_id, address_components
- **Map:** AdvancedMarkerElement with DROP animation

**See:** [Google Maps Integration](docs/features/google-maps/README.md)

### Settings System

Centralized settings management via Filament admin panel.

- **Groups:** booking, map, contact, marketing
- **Service:** SettingsManager singleton
- **Admin:** Custom Filament page at `/admin/system-settings`
- **⚠️ Gotcha:** Use `app()` helper, NOT constructor injection in Livewire

**See:** [Settings System](docs/features/settings-system/README.md)

### Booking System

Multi-step wizard for appointment booking with validation.

- **Steps:** Service → DateTime → Vehicle/Location → Review
- **Integration:** Vehicle management, Google Maps, settings
- **Validation:** Frontend (JS) + Backend (Laravel)
- **Status:** pending → confirmed → in_progress → completed/cancelled

**See:** [Booking System](docs/features/booking-system/README.md)

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

**See:** [Production Build Guide](docs/guides/production-build.md)

## User Model Pattern

**Important:** User model has `first_name` and `last_name` fields, NOT `name` column.

```php
$user->name        // Returns "Jan Kowalski" (via accessor)
$user->first_name  // Returns "Jan"
$user->last_name   // Returns "Kowalski"
```

**Why?** Email templates, Blade views, and third-party packages expect `$user->name`.

**See:** [User Model Documentation](docs/architecture/user-model.md)

## Documentation

📚 **Start here:** [docs/README.md](docs/README.md)

**Key Documentation:**
- [Project Map](docs/project_map.md) - System topology, modules, key files
- [Quick Start](docs/guides/quick-start.md) - Complete setup guide
- [Commands](docs/guides/commands.md) - All available commands
- [Docker](docs/guides/docker.md) - Container architecture
- [Troubleshooting](docs/guides/troubleshooting.md) - Common issues
- [Database Schema](docs/architecture/database-schema.md) - Complete DB structure
- [Architecture Decisions](docs/decisions/) - ADR records

**Polish Note:** Zawsze sprawdzaj `@docs/` przed skanowaniem projektu, żeby nie tracić tokenów na kolejną analizę. Zawsze aktualizuj dokumentację po implementacji nowych rzeczy.

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

**See:** [Troubleshooting Guide](docs/guides/troubleshooting.md)

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

1. **Check documentation:** [docs/README.md](docs/README.md)
2. **Search feature docs:** [docs/features/](docs/features/)
3. **Check logs:** `docker compose logs -f` or `storage/logs/laravel.log`
4. **Enable debug mode:** Set `APP_DEBUG=true` in `.env` (development only)
