# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel 12 car detailing booking application with:
- **Backend:** Laravel 12, PHP 8.2+, MySQL 8.0
- **Frontend:** Vite 7+, Tailwind CSS 4.0
- **Admin Panel:** Filament v4.2.3
- **Queue:** Redis with Laravel Horizon
- **Containerization:** Docker Compose (9 services)

**Local URL:** https://paradocks.local:8444
**Production URL:** https://srv1117368.hstgr.cloud

📚 **Complete Documentation:** [app/docs/README.md](app/docs/README.md)

⚠️ **CRITICAL:** Documentation is in `app/docs/`, NOT `/docs/` in repository root!

## Quick Start

```bash
# One-command setup
./docker-init.sh

# Add domain to hosts
sudo ./add-hosts-entry.sh

# Run migrations with seeders (development only)
docker compose exec app php artisan migrate:fresh --seed

# Create admin user
docker compose exec app php artisan make:filament-user

# Access application
https://paradocks.local:8444
https://paradocks.local:8444/admin
```

**See:** [Quick Start Guide](app/docs/guides/quick-start.md)

## ⚠️ CRITICAL WORKFLOW RULES

### When Uncertain About Technical Knowledge

**ZAWSZE GDY MASZ PROBLEMY Z WIEDZĄ NIE ZGADUJESZ TYLKO ZAWSZE UŻYWASZ @agent-web-research-specialist + Firecrawl!!**

**NEVER guess at:**
- Framework API changes (Filament v3 → v4, Laravel upgrades)
- Package namespaces and class names
- Third-party library usage patterns
- Best practices for new features
- Deprecated vs current methods

**ALWAYS:**
1. Invoke `@agent-web-research-specialist` with Firecrawl
2. Scrape official documentation
3. Apply researched solution
4. Verify with testing
5. Document findings if pattern will be reused

**Example Scenario:**
```
❌ WRONG: See error "Class not found", guess at namespace, try different imports
✅ RIGHT: See error "Class not found", invoke web-research-specialist to research correct namespace in official docs
```

**Why This Matters:**
- Guessing wastes time with trial-and-error
- Can introduce bugs or use deprecated patterns
- Official documentation is authoritative source
- Firecrawl provides accurate, current information

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

# Fresh migrations WITHOUT seeding (preferred)
docker compose exec app php artisan migrate:fresh

# Seed ONLY required reference data
docker compose exec app php artisan db:seed --class=RolePermissionSeeder
docker compose exec app php artisan db:seed --class=EmailTemplateSeeder
docker compose exec app php artisan db:seed --class=VehicleTypeSeeder
docker compose exec app php artisan db:seed --class=ServiceSeeder

# Production deployments (v0.3.1+)
docker compose exec app php artisan migrate --force
```

**⚠️ CRITICAL - Database Seeding Policy:**

**NEVER run `migrate:fresh --seed` or any seeder unless explicitly requested by the user!**

- `--seed` flag creates hundreds of fake users, appointments, and test data
- This clutters the database and makes debugging extremely difficult
- Only seed when user explicitly asks: "please seed the database" or "add test data"
- For development work, use ONLY: `migrate:fresh` (no --seed flag)
- If reference data needed, ask user which specific seeders to run

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

**Services:** app (PHP-FPM), nginx (reverse proxy), mysql, node (Vite), redis, queue, horizon, scheduler, mailpit

**URLs:**
- App: https://paradocks.local:8444
- Admin: https://paradocks.local:8444/admin
- Horizon: https://paradocks.local:8444/horizon
- Mailpit: http://paradocks.local:8025 (email testing)
- Vite: http://paradocks.local:5173

**See:** [Docker Guide](app/docs/guides/docker.md)

## SSL/HTTPS Configuration

**Production URL:** https://srv1117368.hstgr.cloud (HTTP auto-redirects to HTTPS)
**SSL Certificate:** Let's Encrypt (auto-renews via systemd timer)

```bash
# Check certificate status
ssh root@72.60.17.138 "certbot certificates"

# Manual renewal (if needed)
ssh root@72.60.17.138 "certbot renew --cert-name srv1117368.hstgr.cloud"
```

**See:** [ADR-014: SSL/HTTPS Configuration](app/docs/deployment/ADR-014-ssl-https-configuration.md)

## Docker User Model ⚠️ CRITICAL

**Container User:** `laravel:laravel` (UID 1000, GID 1000)
**NOT www-data!** This is a common mistake that causes restart loops.

### Verification Commands:

```bash
# Check user inside container
docker compose exec app whoami  # Should return: laravel

# Check file ownership
docker compose exec app ls -l /var/www/storage  # Should show: laravel laravel
```

**See:** [ADR-013: Docker User Model](app/docs/decisions/ADR-013-docker-user-model.md)

## Filament Admin Panel

**URL:** https://paradocks.local:8444/admin

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
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=your-redis-password

# Google Maps
GOOGLE_MAPS_API_KEY=AIzaSy...
GOOGLE_MAPS_MAP_ID=your_map_id
```

## Feature Documentation

### Email System
Complete transactional email system with queue-based delivery (PL/EN templates).
**See:** [Email System](app/docs/features/email-system/README.md)

### Vehicle Management
Capture vehicle information (type, brand, model, year) for service preparation.
**See:** [Vehicle Management](app/docs/features/vehicle-management/README.md)

### Google Maps Integration
Places Autocomplete for accurate location capture in booking wizard.
**Admin Integration:** Custom Filament map picker for service area management.
**See:** [Google Maps Integration](app/docs/features/google-maps/README.md)
**Known Fix:** [Livewire Re-render Loop Fix](app/docs/fixes/google-maps-picker-livewire-fix.md)

### Settings System
Centralized settings management via Filament admin panel.
**See:** [Settings System](app/docs/features/settings-system/README.md)

### Booking System
Multi-step wizard for appointment booking with validation.
**See:** [Booking System](app/docs/features/booking-system/README.md)

### Staff Scheduling
Calendar-based staff availability with vacation periods and exceptions.
**Admin URLs:**
- `/admin/staff-schedules` - Harmonogramy (base patterns)
- `/admin/staff-vacation-periods` - Urlopy (vacation management)
- `/admin/employees/{id}/edit` → Tabs: Usługi, Harmonogramy, Wyjątki, Urlopy

**See:** [Staff Availability Guide](app/docs/guides/staff-availability.md)

### Content Management System (CMS)
Complete CMS with 4 content types: Pages, Posts, Promotions, Portfolio Items.
**See:** [CMS System Documentation](app/docs/features/cms-system/README.md)

### Service Pages
SEO-friendly pages for each service with Schema.org structured data.
**See:** [Service Pages Documentation](app/docs/features/service-pages/README.md)

### Customer Profile & Settings
User profile management with 5 subpages (personal info, vehicle, address, notifications, security).
**See:** [Customer Profile Documentation](app/docs/features/customer-profile/README.md)

### Maintenance Mode
Professional maintenance mode with Redis-based state management and admin bypass.
**See:** [Maintenance Mode Documentation](app/docs/features/maintenance-mode/README.md)

## Security Audits

**Agent:** `security-audit-specialist`
**Documentation:** [app/docs/security/README.md](app/docs/security/README.md)

Complete security audit system with OWASP Top 10 + GDPR compliance tracking.

### Quick Commands

```bash
# Generate initial baseline (first time, 5-7 min)
Ask agent: "Generate security baseline"

# Ad-hoc security questions (<30 sec)
Ask agent: "Is my booking endpoint secure?"

# Pre-deployment audit (1-2 min incremental scan)
Ask agent: "Run pre-deployment security audit"
```

**See:** [Security Documentation](app/docs/security/README.md)

## Production Build

**IMPORTANT:** This project uses Tailwind CSS 4.0 with `@tailwindcss/vite` plugin.

```bash
# Build production assets
cd app && npm run build

# Verify output
ls -la app/public/build/
cat app/public/build/.vite/manifest.json
```

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

## Git Workflow

**Model:** Gitflow with staging-based release approval
**Documentation:** [CONTRIBUTING.md](CONTRIBUTING.md) | [Git Workflow Guide](app/docs/deployment/GIT_WORKFLOW.md)

### Quick Reference

**Create Feature:**
```bash
git checkout -b feature/my-feature develop
git push -u origin feature/my-feature
# Create PR: feature/my-feature → develop
```

**Create Release** (after staging approval):
```bash
git checkout -b release/v0.3.0 develop
./scripts/release.sh minor  # v0.2.11 → v0.3.0
# Merge to main (triggers deployment)
```

**Emergency Hotfix:**
```bash
git checkout -b hotfix/v0.3.1-patch main
./scripts/release.sh patch  # v0.3.0 → v0.3.1
```

### Branching Strategy

```
main (production, tagged with versions)
  ↑
  └─ release/v0.3.0 ← created after staging approval
      ↑
      └─ develop (integration, auto-deploys to staging)
          ↑
          ├─ feature/security-audit
          ├─ feature/customer-profile
          └─ feature/booking-system
```

### Commit Message Conventions

**Format:** `type(scope): subject`

**Types:** `feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `perf`, `ci`
**Scopes:** `auth`, `booking`, `email`, `admin`, `cms`, `profile`, `ui`, `api`, `db`, `docker`, `ci`

**Examples:**
```bash
feat(booking): add appointment cancellation feature
fix(auth): resolve session fixation vulnerability
docs(readme): update installation instructions
```

**See:** [Git Workflow Guide](app/docs/deployment/GIT_WORKFLOW.md) for complete workflow

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

### Critical Deployment Knowledge (READ FIRST!)

**Essential reading before ANY deployment:**
1. [Deployment History](app/docs/deployment/deployment-history.md) - Complete journey v0.2.1→v0.2.11
2. [Environment Variables](app/docs/deployment/environment-variables.md) - Docker env var hierarchy
3. [Known Issues](app/docs/deployment/known-issues.md) - Quick fixes for critical issues
4. [Dependencies](app/docs/dependencies.md) - Complete stack inventory

**Key Documentation:**
- [Project Map](app/docs/project_map.md) - System topology, modules, key files
- [Staging Server Docs](app/docs/environments/staging/) - Live staging documentation
- [Quick Start](app/docs/guides/quick-start.md) - Complete setup guide
- [Commands](app/docs/guides/commands.md) - All available commands
- [Troubleshooting](app/docs/guides/troubleshooting.md) - Common issues
- [Database Schema](app/docs/architecture/database-schema.md) - Complete DB structure

**Polish Note:** Zawsze sprawdzaj `app/docs/` przed skanowaniem projektu. Dokumentacja jest TYLKO w `app/docs/`, NIE w root `/docs/`. Zawsze aktualizuj dokumentację po implementacji.

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

# Migration fails
docker compose exec app php artisan migrate:fresh --seed
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

**Problem:** Code changes don't take effect, old code still executes.

**Solution (v0.3.1+):** This project uses **opcache-dev.ini** for local development with `validate_timestamps=On`. Code changes apply immediately without container restarts.

**When container restart IS needed:**
- After Composer updates
- After modifying .env file
- Production-like builds

```bash
# Restart containers to clear PHP-FPM OPcache
docker compose restart app horizon queue scheduler

# Then clear Laravel caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan filament:optimize-clear
```

**See:** [Troubleshooting Guide](app/docs/guides/troubleshooting.md)

### Livewire + Alpine.js Integration Issues

**Problem:** Component state resets after user interaction (e.g., map jumps back to default position).

**Root Cause:** `$wire.set()` without third parameter triggers full component re-render, resetting Alpine.js state.

**Solution:** Use deferred updates for real-time UI interactions:

```javascript
// ❌ BAD: Causes re-render loop
this.$wire.set('data.latitude', lat);

// ✅ GOOD: Deferred update, no re-render
this.$wire.set('data.latitude', lat, false);
```

**When to Use:**
- **Deferred (`false`)**: Map interactions, drag events, real-time updates
- **Immediate (default)**: Form submissions, "Save" button clicks

**See:** [Livewire Re-render Loop Fix](app/docs/fixes/google-maps-picker-livewire-fix.md)

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

1. **Check documentation:** [app/docs/README.md](app/docs/README.md)
2. **Search feature docs:** [app/docs/features/](app/docs/features/)
3. **Check logs:** `docker compose logs -f` or `storage/logs/laravel.log`
4. **Enable debug mode:** Set `APP_DEBUG=true` in `.env` (development only)
