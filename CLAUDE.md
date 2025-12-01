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

📚 **Complete Documentation:** [app/docs/README.md](app/docs/README.md)

⚠️ **CRITICAL:** Documentation is in `app/docs/`, NOT `/docs/` in repository root!

## Quick Start

```bash
# One-command setup
./docker-init.sh

# Add domain to hosts
sudo ./add-hosts-entry.sh

# ✅ NEW (v0.1.0+): All seeders run automatically
docker compose exec app php artisan migrate:fresh --seed

# Staff scheduling - SIMPLIFIED NAVIGATION (2 main sections):
# - /admin/staff-schedules (Harmonogramy - base patterns + link to exceptions)
# - /admin/staff-vacation-periods (Urlopy - vacation management)
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

# ✅ UPDATED (v0.1.0): DatabaseSeeder now runs ALL required seeders automatically:
# - SettingSeeder
# - RolePermissionSeeder
# - VehicleTypeSeeder
# - EmailTemplateSeeder
# - SmsTemplateSeeder
#
# ServiceAvailabilitySeeder must be run MANUALLY (requires staff users):
# docker compose exec app php artisan db:seed --class=ServiceAvailabilitySeeder

# NOTE: Staff availability is managed via admin panel (/admin/staff-schedules)
# Use Employee edit page → Harmonogramy tab to set schedules for employees
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

**Services:** app (PHP-FPM), nginx (reverse proxy), mysql, node (Vite), redis, queue, horizon, scheduler, mailpit

**URLs:**
- App: https://paradocks.local:8444
- Admin: https://paradocks.local:8444/admin
- Horizon: https://paradocks.local:8444/horizon
- Mailpit: http://paradocks.local:8025 (email testing)
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
REDIS_CLIENT=phpredis  # Use phpredis (C extension) for performance
REDIS_HOST=redis
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

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

### Staff Scheduling (Simplified UX - 2 Main Sections)

Complete calendar-based staff availability management system with **user-friendly navigation** following industry best practices (Deputy, Homebase, 7shifts).

- **Architecture:** Base Schedules + Date Exceptions + Vacation Periods
- **Database:** 3 active tables (staff_schedules, staff_date_exceptions, staff_vacation_periods)
- **Service:** StaffScheduleService with priority logic (Vacation → Exception → Base Schedule)
- **Admin:** 2 main navigation items (reduced from 4 for better UX)
- **Features:**
  - Recurring weekly schedules with effective date ranges
  - Single-day exceptions (sick days, doctor visits, extra work days)
  - Vacation period management with approval workflow
  - Service-staff assignments via pivot table
  - Calendar-based availability checking (not just recurring weekdays)

**Simplified Navigation (UX-MIGRATION-001):**
- ✅ **"Harmonogramy"** - Main section with base schedules + quick link to exceptions
- ✅ **"Urlopy"** - Vacation management (separate section)
- ❌ ~~"Wyjątki"~~ - Hidden from nav, accessible via Harmonogramy header actions
- ❌ ~~"Dostępności"~~ - DELETED (legacy ServiceAvailabilityResource)

**Admin URLs:**
- `/admin/staff-schedules` - Harmonogramy (base patterns + "Zarządzaj wyjątkami" button)
- `/admin/staff-date-exceptions` - Wyjątki (accessible via header action, not in nav)
- `/admin/staff-vacation-periods` - Urlopy (separate section)
- `/admin/employees/{id}/edit` → Tabs: Usługi, Harmonogramy, Wyjątki, Urlopy

**Key Files:**
- `app/Services/StaffScheduleService.php` - Core availability logic
- `app/Services/AppointmentService.php` - Integrated with new system
- `app/Models/StaffSchedule.php` - Base patterns
- `app/Models/StaffDateException.php` - Day overrides
- `app/Models/StaffVacationPeriod.php` - Vacation ranges
- `database/migrations/2025_11_19_*` - All schema migrations

**UX Migration Notes (UX-MIGRATION-001):**
- **Navigation reduced from 4 → 2 items** (50% cognitive load reduction)
- Follows industry best practices (Deputy, Homebase, 7shifts all use 1-2 sections)
- StaffDateExceptionResource hidden from nav, accessible via header actions
- ServiceAvailabilityResource completely deleted (legacy cleanup)
- Research: https://medium.com/@pnaylor09/a-ux-case-study-on-designing-a-time-off-management-web-app-8b3151fa397d

**Usage Example:**
1. Create base schedule: Jan works Mon-Fri 9:00-17:00
2. Add exception: Jan unavailable 2025-12-24 (Christmas Eve)
3. Add vacation: Jan on vacation 2025-07-01 to 2025-07-14
4. Assign services: Jan can perform "Detailing wewnętrzny" + "Korekta lakieru"
5. System checks availability: Vacation (blocks all) → Exception (overrides schedule) → Base schedule

**See:** [Staff Availability Guide](app/docs/guides/staff-availability.md)

### Content Management System (CMS)

Complete content management system with 4 content types, Filament admin panel, and public frontend.

- **Content Types:** Pages, Posts, Promotions, Portfolio Items
- **Admin URLs:**
  - Pages: `/admin/pages`
  - Posts: `/admin/posts` (Aktualności)
  - Promotions: `/admin/promotions`
  - Portfolio: `/admin/portfolio-items`
  - Categories: `/admin/categories`
- **Public URLs:**
  - Pages: `/strona/{slug}` (e.g., `/strona/o-nas`)
  - Posts: `/aktualnosci/{slug}` (e.g., `/aktualnosci/nowa-promocja`)
  - Promotions: `/promocje/{slug}` (e.g., `/promocje/rabat-50`)
  - Portfolio: `/portfolio/{slug}` (e.g., `/portfolio/detailing-bmw`)

**Content Features:**
- **Hybrid content system:** RichEditor (main body) + Builder (advanced blocks)
- **Content blocks:** image, gallery, video, CTA, columns, quotes
- **SEO fields:** meta_title, meta_description, featured_image
- **Publishing states:** draft (no published_at), scheduled (future date), published (past date)
- **Categories:** Hierarchical categories for Posts/Portfolio (type: 'post' or 'portfolio')
- **Before/After images:** Portfolio items showcase project transformations
- **Preview buttons:** Open frontend in new tab (eye icon in admin)
- **Auto-slug:** Generated from title on blur

**Database Tables:**
- `pages` - Static content with layout options (default, full-width, minimal)
- `posts` - Blog/news with excerpt and category
- `promotions` - Offers with active flag and valid_from/valid_until dates
- `portfolio_items` - Projects with before_image, after_image, gallery JSON
- `categories` - Hierarchical categories (parent_id, type field)

**Key Models:**
- `Page`, `Post`, `Promotion`, `PortfolioItem`, `Category`
- Scopes: `published()`, `draft()`, `active()`, `valid()`
- Relationships: Post/Portfolio → Category, Category → parent/children

**Content Workflow:**
1. Create content in admin (auto-draft)
2. Add main content in RichEditor (body field)
3. Optionally add advanced blocks (image, gallery, video, CTA, etc.)
4. Set SEO fields (meta_title, meta_description, featured_image)
5. Set published_at for scheduling or leave empty for draft
6. Preview with eye icon button (opens frontend in new tab)

**See:** [CMS System Documentation](app/docs/features/cms-system/README.md)

### Customer Profile & Settings

Complete user profile management system with sidebar navigation and 5 dedicated subpages.

- **Architecture:** Separate subpages (NOT tabs) with shared sidebar layout
- **Service Layer:** ProfileService, UserAddressService, UserVehicleService
- **Address Integration:** Google Maps Places Autocomplete (same as booking wizard)
- **Validation:** Form Requests with `prepareForValidation()` for JSON decoding

**Public URLs (Polish):**
- `/moje-konto` - Redirects to `/moje-konto/dane-osobowe`
- `/moje-konto/dane-osobowe` - Personal info (name, phone)
- `/moje-konto/pojazd` - Vehicle management (type, brand, model, year)
- `/moje-konto/adres` - Address with Google Maps autocomplete
- `/moje-konto/powiadomienia` - Notification preferences (SMS, email, marketing)
- `/moje-konto/bezpieczenstwo` - Password change, email change, account deletion

**Key Files:**
- `app/Http/Controllers/ProfileController.php` - 5 view methods + update handlers
- `app/Http/Controllers/UserVehicleController.php` - CRUD for vehicles
- `app/Http/Controllers/UserAddressController.php` - CRUD for addresses
- `resources/views/profile/layout.blade.php` - Responsive sidebar layout
- `resources/views/profile/pages/*.blade.php` - 5 subpage views
- `resources/views/profile/partials/icons/*.blade.php` - SVG icons

**UX Design (ADR: Separate Subpages vs Tabs):**
- **Problem:** Tab-based navigation loses state after form submission
- **Solution:** Separate subpages with sidebar navigation
- **Mobile:** Horizontal scrollable navigation bar
- **Desktop:** Vertical sidebar with active state highlighting
- **Benefit:** Direct URL access, proper form redirect handling, browser history support

**Form Request Pattern (JSON Components):**
```php
// StoreAddressRequest.php, UpdateAddressRequest.php
protected function prepareForValidation(): void
{
    if ($this->has('components') && is_string($this->components)) {
        $decoded = json_decode($this->components, true);
        $this->merge([
            'components' => is_array($decoded) ? $decoded : null,
        ]);
    }
}
```

**See:** [Customer Profile Documentation](app/docs/features/customer-profile/README.md)

### Maintenance Mode

Professional maintenance mode system with Redis-based state management, multiple maintenance types, and Docker-aware architecture.

- **Types**: Deployment (60s), Pre-launch (3600s), Scheduled (300s), Emergency (120s)
- **State Storage**: Redis (`maintenance:mode`, `maintenance:config`, `maintenance:enabled_at`, `maintenance:secret_token`)
- **Bypass Methods**: Role-based (admin, super-admin), secret token, NO bypass (pre-launch)
- **Nginx Optimization**: Pre-launch mode uses file trigger + static HTML (zero PHP overhead)
- **Filament UI**: `/admin/system/maintenance-mode` - Enable/disable with visual status indicators
- **Audit Trail**: All events logged to `maintenance_events` table with user, IP, metadata

**CLI Commands**:
```bash
# Enable maintenance
docker compose exec app php artisan maintenance:enable --type=deployment --message="System update" --duration="15 minutes"

# Check status
docker compose exec app php artisan maintenance:status --history

# Disable maintenance
docker compose exec app php artisan maintenance:disable --force
```

**Key Files**:
- `app/Services/MaintenanceService.php` - Core business logic (15+ methods)
- `app/Enums/MaintenanceType.php` - DEPLOYMENT, PRELAUNCH, SCHEDULED, EMERGENCY
- `app/Http/Middleware/CheckMaintenanceMode.php` - Request filtering middleware
- `app/Filament/Pages/MaintenanceSettings.php` - Admin panel page
- `resources/views/errors/maintenance-*.blade.php` - Custom error pages
- `docker/nginx/app.conf` - Nginx pre-launch file check

**Bypass Examples**:
```php
// Role-based bypass (automatic for admins)
if (Auth::user()->hasAnyRole(['super-admin', 'admin'])) {
    // Access granted
}

// Secret token bypass
https://paradocks.local:8444?maintenance_token=paradocks-xxxxx...

// Programmatic usage
$service = app(MaintenanceService::class);
$service->enable(MaintenanceType::DEPLOYMENT, Auth::user(), ['message' => 'Deploying v2.0']);
```

**Important Notes**:
- **PRELAUNCH mode**: Complete lockdown, NO bypass allowed (not even admins!)
- **Deployment mode**: Admins can bypass, secret token generated
- **Redis keys persist** across container restarts (not file-based like Laravel's default)
- **Nginx serves static HTML** for pre-launch (no PHP processing)
- **Health endpoint** `/up` bypasses maintenance for Docker healthchecks

**See:** [Maintenance Mode Documentation](app/docs/features/maintenance-mode/README.md)

## Security Audits

**Agent**: `security-audit-specialist`
**Documentation**: [app/docs/security/README.md](app/docs/security/README.md)

Complete security audit system with OWASP Top 10 + GDPR compliance tracking.

- **Use Cases**: Ad-hoc security questions, vulnerability scanning, pre-deployment audits
- **Coverage**: Laravel security, Docker/VPS hardening, DevOps (GitHub Actions), GDPR
- **Features**: Smart caching (instant responses), guided remediation, collaboration with laravel-senior-architect
- **Documentation**: Vulnerability tracking, remediation guides, audit reports, project-specific patterns

### Quick Commands

```bash
# Generate initial baseline (first time, 5-7 min)
Ask agent: "Generate security baseline"

# Ad-hoc security questions (<30 sec)
Ask agent: "Is my booking endpoint secure?"
Ask agent: "How do I prevent SQL injection?"
Ask agent: "Check Docker security"

# Pre-deployment audit (1-2 min incremental scan)
Ask agent: "Run pre-deployment security audit"

# Fix vulnerability (hands off to laravel-senior-architect)
Ask agent: "Fix VULN-001"
```

### Capabilities

✅ **OWASP Top 10 2021** - Comprehensive vulnerability detection patterns
✅ **Laravel Security** - Mass assignment, IDOR, XSS, SQL injection, CSRF, Filament authorization
✅ **Infrastructure Security** - Docker (exposed ports, secrets), VPS (Ubuntu, Nginx, UFW), CI/CD
✅ **GDPR Compliance** - Consent tracking, data retention, audit logging, right to erasure
✅ **Smart Caching** - File checksums for change detection, instant responses from cached baseline
✅ **Guided Remediation** - Code examples (vulnerable → secure), effort estimates, step-by-step instructions

### Documentation Structure

```
app/docs/security/
├── README.md           # Security hub
├── baseline.md         # Current security posture (cached)
├── compliance.md       # OWASP + GDPR checklist
├── vulnerabilities/    # VULN-001, VULN-002, ...
├── remediation-guides/ # SQL injection, XSS, rate limiting, etc.
├── audit-reports/      # Historical scans
└── patterns/           # Project-specific security (service layer, maintenance mode)
```

### First-Time Setup

```bash
# 1. Ask agent to generate baseline
"Generate security baseline"

# Agent will (5-7 minutes):
# - Scan routes, models, controllers, middleware
# - Detect OWASP Top 10 vulnerabilities
# - Generate baseline.md with risk profile
# - Create vulnerability docs for CRITICAL/HIGH issues
# - Provide prioritized fix list

# 2. Address critical vulnerabilities
# - VULN-001: Missing rate limiting (30 min)
# - VULN-003: Exposed Docker ports (15 min)
# - VULN-002: Plaintext API tokens (2 hours)

# 3. Run before each deployment
"Run pre-deployment security audit"
```

### Current Security Status (Expected)

**Risk Profile**: 🟡 **MODERATE** (Acceptable for MVP, hardening recommended)
**OWASP Compliance**: 60% (6/10 categories passed)
**GDPR Compliance**: 50% (3/6 requirements met)

**Critical Issues** (discovered during research):
- 🔴 Missing rate limiting on auth/booking endpoints
- 🔴 Exposed Docker ports (MySQL 3306, Redis 6379)
- 🔴 Plaintext API tokens in database

**See**: [Security Baseline](app/docs/security/baseline.md) after first scan

### Security Workflow

**Daily Development**:
```markdown
Developer: "I'm adding a file upload feature"
Agent: "💡 Security Checklist: validate magic bytes, limit file size,
        sanitize filenames, store outside webroot, serve via controller"
```

**Pre-Deployment**:
```markdown
Developer: "Run pre-deployment security audit"
Agent: [Detects changed files via checksums]
       [Scans only changed files - 1-2 min]
       [Blocks deployment if CRITICAL issues found]
       "🔴 2 CRITICAL vulnerabilities block deployment. Fix or override?"
```

**Remediation**:
```markdown
Developer: "Fix VULN-001"
Agent: [Reads vulnerability doc]
       [Provides code examples + step-by-step guide]
       "Hand off to laravel-senior-architect for implementation? [y/N]"
```

**See**: [Security Documentation](app/docs/security/README.md)

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

## Git Workflow

**Model**: Gitflow with staging-based release approval
**Documentation**: [CONTRIBUTING.md](CONTRIBUTING.md) | [docs/deployment/GIT_WORKFLOW.md](app/docs/deployment/GIT_WORKFLOW.md)

### Quick Reference

**Create Feature**:
```bash
git checkout -b feature/my-feature develop
# ... develop ...
git push -u origin feature/my-feature
# Create PR: feature/my-feature → develop
```

**Create Release** (after staging approval):
```bash
git checkout -b release/v0.3.0 develop
# Update CHANGELOG.md, bump versions
./scripts/release.sh minor  # v0.2.11 → v0.3.0
# Merge to main (triggers deployment)
```

**Emergency Hotfix**:
```bash
git checkout -b hotfix/v0.3.1-patch main
# ... fix ...
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

**Primary Branches** (long-lived, protected):
- `main` - Production-ready code (requires PR + review)
- `develop` - Integration branch (requires PR)
- `staging` - Auto-deploys from develop

**Supporting Branches** (short-lived, auto-deleted):
- `feature/*` - New features (from develop)
- `release/*` - Release preparation (from develop, after staging approval)
- `hotfix/*` - Emergency production fixes (from main)

### Tagging Strategy

**Production Tags** (on main):
- `v0.3.0` - Minor release (new features)
- `v0.3.1` - Patch release (bug fixes)
- `v1.0.0` - Major release (breaking changes or production-ready)

**Pre-Release Tags** (optional, on release/*):
- `v0.3.0-rc1` - Release candidate
- `v0.3.0-staging` - Deployed to staging

**Semantic Versioning**:
- **MAJOR**: Breaking changes or production launch (v0.x.x → v1.0.0)
- **MINOR**: New features, backward-compatible (v0.3.0 → v0.4.0)
- **PATCH**: Bug fixes, security patches (v0.3.0 → v0.3.1)

### Commit Message Conventions

**Format**: `type(scope): subject`

**Types**: `feat`, `fix`, `docs`, `refactor`, `test`, `chore`, `perf`, `ci`
**Scopes**: `auth`, `booking`, `email`, `admin`, `cms`, `profile`, `ui`, `api`, `db`, `docker`, `ci`

**Examples**:
```bash
feat(booking): add appointment cancellation feature
fix(auth): resolve session fixation vulnerability
docs(readme): update installation instructions
refactor(services): extract email logic to service class
test(appointment): add integration tests for booking flow
chore(deps): upgrade Laravel to 12.32.5
```

### Workflow

**1. Feature Development**:
```bash
# Create feature branch from develop
git checkout develop
git pull origin develop
git checkout -b feature/customer-profile

# Develop feature (atomic commits)
git commit -m "feat(profile): add profile page"
git commit -m "feat(profile): add validation"
git commit -m "test(profile): add tests"

# Push and create PR
git push -u origin feature/customer-profile
# Create PR: feature/customer-profile → develop
# After approval → merge (squash recommended)
# Branch auto-deleted ✅
```

**2. Deploy to Staging** (auto):
```bash
# develop → staging (auto-deploy via CI/CD)
# Test on https://staging.paradocks.com
# Verify: ✅ works, ✅ no regressions, ✅ ready for production
```

**3. Create Release** (after staging approval):
```bash
# Create release branch from develop
git checkout -b release/v0.3.0 develop

# Update CHANGELOG.md
# Bump versions in package.json, composer.json

# Push release branch
git push -u origin release/v0.3.0
```

**4. Deploy to Production**:
```bash
# Merge release to main
git checkout main
git merge --no-ff release/v0.3.0

# Create production tag (triggers deployment)
git tag -a v0.3.0 -m "Release v0.3.0 - Customer Profile Feature

Added:
- Customer profile management
- Google Maps integration

See: CHANGELOG.md"

git push origin main v0.3.0

# Merge back to develop
git checkout develop
git merge --no-ff release/v0.3.0

# Delete release branch
git branch -d release/v0.3.0
git push origin --delete release/v0.3.0
```

### Branch Protection Rules

**`main` branch**:
- ✅ Require PR + 1 approval
- ✅ Status checks must pass (tests, lint)
- ❌ Force push disabled
- ❌ Deletion disabled

**`develop` branch**:
- ✅ Require PR
- ✅ Status checks must pass
- ❌ Force push disabled

**`staging` branch**:
- Auto-managed by CI/CD
- No direct commits

### Release Script

```bash
# Automated tagging with version bump
./scripts/release.sh patch  # v0.3.0 → v0.3.1
./scripts/release.sh minor  # v0.3.1 → v0.4.0
./scripts/release.sh major  # v0.4.0 → v1.0.0
```

**What the script does**:
1. Validates git state (clean, on main)
2. Fetches latest tags
3. Bumps version (semantic versioning)
4. Creates annotated tag
5. Pushes tag to origin
6. Triggers GitHub Actions deployment

**See**:
- [CONTRIBUTING.md](CONTRIBUTING.md) - Contributor guidelines
- [Git Workflow Guide](app/docs/deployment/GIT_WORKFLOW.md) - Detailed workflow
- [CHANGELOG.md](CHANGELOG.md) - Version history
- [CI/CD Deployment Runbook](app/docs/deployment/runbooks/ci-cd-deployment.md) - Deployment procedures

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
1. **[Deployment History](app/docs/deployment/deployment-history.md)** - Complete journey v0.2.1→v0.2.11, all failures and solutions
2. **[Environment Variables](app/docs/deployment/environment-variables.md)** - Docker env var hierarchy (root cause of v0.2.5-v0.2.7 failures!)
3. **[Known Issues](app/docs/deployment/known-issues.md)** - Quick fixes for 12 critical issues
4. **[Dependencies](app/docs/dependencies.md)** - Complete stack inventory (Laravel 12.32.5, PHP 8.2, etc.)

**Key Insight:** Docker environment variables MUST be in `docker-compose.yml` environment section. Being in `.env` file alone is NOT enough. This pattern caused 3 consecutive deployment failures.

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

### Permission Denied: storage/framework/views

**Status:** ✅ **SOLVED** (v0.1.0+)

**Symptoms** (Historical):
```
ERROR  Failed to enter maintenance mode: file_put_contents(storage/framework/views/xxx.php): Permission denied
```

**Root Cause** (Historical):
The old deployment workflow had a CATCH-22 problem:
- Maintenance mode tried to write files BEFORE building new container
- Old container had wrong UID (1000) but files owned by UID 1002
- Permission denied → Build never happened → Deployment failed

**Current Solution** (v0.1.0+):
GitHub Actions now uses **zero-downtime healthcheck deployment**:
1. Old container continues serving (no maintenance mode)
2. Build new container with correct UID in background
3. Wait for new container to be healthy
4. Run migrations (~15s controlled downtime)
5. Switch traffic to new container
6. No permission errors

**For Local Development:**
```bash
# If you encounter permission issues locally:
docker compose build --no-cache app
docker compose restart app
```

**See:**
- [DEPLOYMENT.md - Deployment Strategy](DEPLOYMENT.md#deployment-strategy) for complete details
- [ADR-011](app/docs/deployment/ADR-011-healthcheck-deployment-strategy.md) for architectural decision

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
