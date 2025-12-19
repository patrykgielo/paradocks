# Paradocks - Documentation Hub

**Last Updated:** December 14, 2025

Centralna nawigacja po dokumentacji projektu Laravel 12 + Filament 4.2.3.

## 🚀 Quick Start

**Nowy developer?** Zobacz:
1. **[CLAUDE.md](../CLAUDE.md)** - Quick reference + essential commands
2. **[Project Map](./project_map.md)** - High-level system overview
3. **[Planned Features](./features/planned/)** - 🆕 Future features ready for implementation

---

## 🎯 Planned Features

**Status:** 🟡 Designed, not yet implemented | **Docs:** [`features/planned/`](./features/planned/)

Features with complete implementation plans, research, and technical decisions ready to execute:

- **[Visual Redesign v4.0 - Monochrome](./features/planned/visual-redesign-v4.0-monochrome.md)** - Professional monochrome color system (8-9h effort)

See **[Planned Features README](./features/planned/README.md)** for full roadmap and how to use these plans.

---

## 📚 Features Documentation

### Email System & Notifications
**Status:** ✅ Production Ready | **Docs:** [`features/email-system/`](./features/email-system/)

Complete transactional email system with queue-based delivery, multi-language support (PL/EN), and Filament admin panel.

- **[Overview & Quick Start](./features/email-system/README.md)** - Getting started
- **[Architecture](./features/email-system/architecture.md)** - Services, Models, Events, Design Patterns
- **[Templates](./features/email-system/templates.md)** - Template management, variables, Blade syntax
- **[Notifications](./features/email-system/notifications.md)** - Events & notifications, event-driven flow
- **[Scheduled Jobs](./features/email-system/scheduled-jobs.md)** - Reminders, follow-ups, digests, cleanup
- **[Filament Admin](./features/email-system/filament-admin.md)** - Admin panel resources, permissions
- **[Troubleshooting](./features/email-system/troubleshooting.md)** - Common issues and fixes

**Quick Links:**
- Test Send button fixed ✅ (November 2025)
- 18 templates (9 types × PL/EN)
- Gmail SMTP with App Password
- Redis queues + Horizon
- Idempotency via message_key

---

### Vehicle Management
**Status:** ✅ Production Ready | **Docs:** [`features/vehicle-management/`](./features/vehicle-management/)

Booking system integration - capture vehicle type, brand, model, year.

- **[Full Documentation](./features/vehicle-management/README.md)** - Database schema, API endpoints, Filament resources

**Key Features:**
- 5 Vehicle Types (seeded)
- Dynamic Brands & Models (admin-managed)
- Many-to-Many relation (vehicle type ↔ model)
- Booking wizard integration (Step 3)

---

### Google Maps Integration
**Status:** ✅ Production Ready | **Docs:** [`features/google-maps/`](./features/google-maps/)

Address autocomplete + location capture using Google Maps JavaScript API (NOT Web Components).

- **[Full Documentation](./features/google-maps/README.md)** - Setup, integration, troubleshooting

**Implementation:**
- Modern JavaScript API (`google.maps.places.Autocomplete`)
- AdvancedMarkerElement (latest marker API)
- Location data: address, lat/lng, place_id, components
- Database storage in `appointments` table

---

### Settings System
**Status:** ✅ Production Ready | **Docs:** [`features/settings-system/`](./features/settings-system/)

Centralized configuration management via Filament admin panel + SettingsManager service.

- **[Full Documentation](./features/settings-system/README.md)** - Architecture, usage, API reference

**Setting Groups:**
- **booking** - Business hours, slot intervals, advance booking rules
- **map** - Google Maps configuration
- **contact** - Email, phone, address
- **marketing** - Homepage content (hero, features, CTA)

---

### Booking System
**Status:** ✅ Production Ready | **Docs:** [`features/booking-system/`](./features/booking-system/)

Multi-step appointment booking wizard (4 steps, vanilla JavaScript).

- **[Full Documentation](./features/booking-system/README.md)** - Wizard flow, API endpoints, database schema

**Features:**
- Service selection → Date/Time → Vehicle & Location → Confirmation
- Guava Calendar integration
- Google Maps autocomplete
- Queue-based processing
- Email confirmations

---

### SMS System
**Status:** ✅ Production Ready | **Docs:** [`features/sms-system/`](./features/sms-system/)

Complete SMS notification system with SMSAPI.pl integration.

- **[Overview & Quick Start](./features/sms-system/README.md)** - Getting started
- **[Architecture](./features/sms-system/architecture.md)** - Services, Models, Events
- **[Templates](./features/sms-system/templates.md)** - Template management
- **[SMSAPI Integration](./features/sms-system/smsapi-integration.md)** - API configuration

**Quick Links:**
- 14 SMS templates (7 types × PL/EN)
- SMSAPI.pl gateway
- Queue-based delivery
- Webhook support (delivery status, incoming)

---

### Invoice PDF Generation System
**Status:** 📋 PLANNED (pending budget approval) | **Docs:** [`features/invoice-pdf-system/`](./features/invoice-pdf-system/) | **Estimate:** 1,700 PLN

Automatic VAT invoice PDF generation for appointments with invoice data requested.

- **[Overview & Quick Start](./features/invoice-pdf-system/README.md)** - Feature documentation
- **[Installation Guide](./features/invoice-pdf-system/installation.md)** - Step-by-step deployment (30 min)
- **[Architecture](./features/invoice-pdf-system/architecture.md)** - Technical details & data flow
- **[Implementation Plan](./features/invoice-pdf-system/implementation-plan.md)** - Complete scope of work
- **[Commercial Estimate](./estimates/wycena-kompletny-system-faktur-3200-pln.md)** - Client pricing (3,200 PLN total)
- **[Retrospective Analysis](./estimates/analiza-retrospektywna-etap-1-invoice-data.md)** - Etap 1 cost review

**Key Features:**
- Automatic invoice numbering (FV/YYYY/MM/XXXX)
- PDF generation (Spatie Laravel-PDF + mPDF fallback)
- Price snapshot at booking (historical accuracy)
- Authorization (customer/admin/assigned staff)
- Rate limiting (10 downloads/min)
- Filament integration (Settings tab + download action)
- Customer panel download button

**ROI:**
- Investment: 1,700 PLN
- Monthly savings: 755 PLN
- Annual ROI: 283%
- Payback period: 4.2 months (~20 days)

---

### Content Management System (CMS)
**Status:** ✅ Production Ready | **Docs:** [`features/cms-system/`](./features/cms-system/)

Complete content management system with 4 content types, Filament admin panel, and public frontend.

- **[Overview & Quick Start](./features/cms-system/README.md)** - Getting started guide
- **[Content Types](./features/cms-system/content-types.md)** - Pages, Posts, Promotions, Portfolio reference
- **[Admin Panel Guide](./features/cms-system/admin-panel.md)** - Filament Resources walkthrough
- **[Frontend Rendering](./features/cms-system/frontend.md)** - Controllers, routes, Blade views
- **[Content Blocks](./features/cms-system/content-blocks.md)** - Builder blocks reference

**Content Types:**
- **Pages** (`/strona/{slug}`) - Static pages with custom layouts (About, Services, Contact)
- **Posts** (`/aktualnosci/{slug}`) - Blog posts/news articles with categories
- **Promotions** (`/promocje/{slug}`) - Special offers and campaigns
- **Portfolio** (`/portfolio/{slug}`) - Project showcase with before/after images

**Key Features:**
- Hybrid content system: RichEditor (main body) + Builder (advanced blocks)
- Content blocks: image, gallery, video, CTA, columns, quotes
- SEO fields: meta_title, meta_description, featured_image
- Publishing states: draft → scheduled → published
- Categories for Posts/Portfolio (hierarchical)
- Before/After images for Portfolio Items
- Preview buttons (open frontend in new tab)
- Auto-slug generation from title

---

### Role-Based Access Control (RBAC)
**Status:** ✅ Production Ready | **Docs:** [`features/role-based-access/`](./features/role-based-access/)

Complete role-based authorization system for Filament admin panel with granular permissions.

- **[Full Documentation](./features/role-based-access/README.md)** - Roles, permissions, authorization patterns

**Roles:**
- **super-admin** - Full system access
- **admin** - Full admin panel access
- **staff** - Limited to Appointments + Own Vacations
- **customer** - No admin panel access

**Key Features:**
- Spatie Laravel Permission integration
- Resource-level authorization (`canViewAny()`)
- Record ownership checks (staff see only own data)
- Query scoping for data isolation
- Field-level visibility control
- Permission-based + role-based authorization

**Staff Restrictions (Phase 2):**
- ✅ Cannot access System Settings
- ✅ Cannot view Email Logs/Events
- ✅ Cannot see approval toggle in vacation form
- ✅ Can only manage own pending vacations
- ✅ Cannot create vacations for other employees

---

## 🔧 Bug Fixes & Solutions

**Directory:** [`fixes/`](./fixes/)

Detailed documentation for critical bug fixes with root cause analysis, solutions, and prevention strategies.

### Recent Fixes

**Google Maps Picker Livewire Re-render Fix** (December 2025)
- **Issue:** Map resets to Warsaw after autocomplete selection or marker dragging
- **Root Cause:** Livewire/Alpine.js state conflict - missing third parameter in `$wire.set()`
- **Solution:** Added `, false` for deferred updates without re-rendering
- **Impact:** Critical - broke all service area edits in admin panel
- **Docs:** [Livewire Re-render Loop Fix](./fixes/google-maps-picker-livewire-fix.md)

**Alpine.js Button Click Fix** (December 2025)
- **Issue:** Button clicks not registering in Filament components
- **Solution:** Adjusted Alpine.js event binding and CSS pointer-events
- **Docs:** [Alpine Button Click Fix](./fixes/ALPINE-BUTTON-CLICK-FIX.md)

### Common Patterns

**Livewire + Alpine.js Integration:**
```javascript
// ✅ CORRECT: Use deferred updates for real-time UI
this.$wire.set('data.field', value, false);

// ❌ WRONG: Triggers re-render loop
this.$wire.set('data.field', value);
```

**See:** [Fixes Index](./fixes/README.md) for complete list and prevention checklist

---

## 🏗️ Architecture

- **[Project Map](./project_map.md)** - Complete system overview, domain model, relationships
- **[Database Schema](./architecture/database-schema.md)** - ERD, tables, indexes
- **[Technology Stack](./architecture/technology-stack.md)** - Complete technology stack with versions

---

## 🚀 Deployment & Operations

### Environment Documentation

Comprehensive "live" documentation for each deployed environment, reflecting **actual state** with real configurations, credentials, and workarounds.

#### Staging Environment

- **[00-SERVER-INFO.md](./environments/staging/00-SERVER-INFO.md)** - Quick reference (IP: 72.60.17.138, SSH, emergency commands)
- **[01-DEPLOYMENT-LOG.md](./environments/staging/01-DEPLOYMENT-LOG.md)** - Complete deployment history (2025-11-11)
- **[02-CONFIGURATIONS.md](./environments/staging/02-CONFIGURATIONS.md)** - All configs (Docker, Nginx, PHP, MySQL, Redis)
- **[03-CREDENTIALS.md](./environments/staging/03-CREDENTIALS.md)** - Passwords & secrets (⚠️ EXCLUDED FROM GIT)
- **[04-SERVICES.md](./environments/staging/04-SERVICES.md)** - Docker service management (6 containers)
- **[05-ISSUES-WORKAROUNDS.md](./environments/staging/05-ISSUES-WORKAROUNDS.md)** - 6 deployment issues & solutions
- **[06-MAINTENANCE.md](./environments/staging/06-MAINTENANCE.md)** - Daily/weekly/monthly procedures
- **[07-NEXT-STEPS.md](./environments/staging/07-NEXT-STEPS.md)** - Pending tasks (SSL, SMTP, backups)

**Quick Access:**
```bash
# SSH to staging
ssh ubuntu@72.60.17.138

# Check services status
docker-compose -f docker-compose.prod.yml ps

# View application logs
docker-compose -f docker-compose.prod.yml logs -f app
```

**Server Status:**
- **Environment:** Staging VPS (Ubuntu 24.04 LTS)
- **Hostname:** srv1117368.hstgr.cloud
- **Services:** MySQL 8.0, Redis 7.2, PHP 8.2, Nginx 1.25, Horizon, Scheduler (all healthy ✅)
- **Deployed:** 2025-11-11
- **Branch:** staging

**Critical Workarounds Documented:**
- UFW-Docker security integration (prevents Docker bypassing firewall)
- Storage volume removal (permission issues resolved)
- Vite manifest symlink (Laravel asset helper compatibility)
- MySQL password reset procedure
- Nginx config without paradocks-node references

**Production Environment:**
Documentation will follow the same structure when deployed.

---

## 📐 Architecture

### Technology Stack

Complete technology stack with versions: [architecture/technology-stack.md](./architecture/technology-stack.md)

**Backend:** Laravel 12.32.5, PHP 8.2.29, MySQL 8.0, Redis 7.2
**Frontend:** Vite 7.1.9, Tailwind CSS 4.0, Livewire 3.6.4
**Admin:** Filament v4.2.3
**DevOps:** Docker 29.0.0, Compose 2.40.3, Ubuntu 24.04 LTS

---

## 📝 Decisions (ADRs)

Architecture Decision Records documenting major technical choices:

**Deployment ADRs** (November 2025 - Staging VPS):
- **[ADR-007: UFW-Docker Security Integration](./deployment/ADR-007-ufw-docker-security.md)** - Firewall integration to prevent Docker bypass
- **[ADR-008: Storage Volume Removal](./deployment/ADR-008-storage-volume-removal.md)** - Resolved permission issues by removing bind mounts
- **[ADR-009: Vite Manifest Symlink](./deployment/ADR-009-vite-manifest-symlink.md)** - Laravel asset helper compatibility with Vite 7

**Application ADRs** (October/November 2025):
- **[ADR-004: Automatic Staff Assignment](./decisions/ADR-004-automatic-staff-assignment.md)** - Removed manual staff selection
- **[ADR-005: Business Hours Configuration](./decisions/ADR-005-business-hours-config.md)** - Centralized booking rules
- **[ADR-006: User Model Name Accessor](./decisions/ADR-006-user-model-name-accessor.md)** - first_name + last_name → name

**Original ADRs** (2025-10-12):
- **[ADR-001: Service Layer Architecture](./decision_log/ADR-001-service-layer-architecture.md)** - Business logic extraction
- **[ADR-002: Appointment Time Slot System](./decision_log/ADR-002-appointment-time-slot-system.md)** - Recurring weekly availability
- **[ADR-003: Role-Based Access Control](./decision_log/ADR-003-role-based-access-control.md)** - Spatie Permission

---

## 🧪 Testing

- **[testing/](./testing/)** - Test documentation

**Quick Commands:**
```bash
# Run all tests
php artisan test

# Coverage report
php artisan test --coverage

# Specific suite
php artisan test --testsuite=Feature
```

---

## 📦 Archive

Old/temporary documentation (kept for reference):

- **[archive/](./archive/)** - Deprecated docs
- `email-system-phase-3-summary.md` (archived)
- `email-system-quick-reference.md` (archived)
- `PROFILE_SYNC_IMPLEMENTATION.md` (archived)

---

## 📐 Documentation Structure

```
docs/
├── README.md (you are here)          # Navigation hub
├── project_map.md                    # High-level overview
├── features/                         # Feature-specific docs
│   ├── email-system/                 # 7 granular files
│   │   ├── README.md
│   │   ├── architecture.md
│   │   ├── templates.md
│   │   ├── notifications.md
│   │   ├── scheduled-jobs.md
│   │   ├── filament-admin.md
│   │   └── troubleshooting.md
│   ├── vehicle-management/
│   │   └── README.md
│   ├── google-maps/
│   │   └── README.md
│   ├── settings-system/
│   │   └── README.md
│   └── booking-system/
│       └── README.md
├── fixes/                            # Bug fixes with root cause analysis
│   ├── README.md                     # Fixes index + common patterns
│   ├── google-maps-picker-livewire-fix.md
│   └── ALPINE-BUTTON-CLICK-FIX.md
├── decisions/                        # ADRs (new)
├── decision_log/                     # ADRs (original)
├── edge-cases/                       # Edge case analysis
├── testing/                          # Test documentation
└── archive/                          # Old docs
```

---

## 🔧 Technology Stack

**Backend:**
- Laravel 12
- PHP 8.2+
- MySQL 8.0 (Docker)
- Redis (queues, cache)
- Laravel Horizon
- Laravel Filament v4.2.3
- Spatie Laravel Permission v6.21
- Guava Calendar v2.0

**Frontend:**
- Vite 7
- Tailwind CSS 4.0
- Blade templates
- Vanilla JavaScript (no framework)

**DevOps:**
- Docker (Nginx, PHP-FPM, MySQL, Redis, Node.js)
- Laravel Pint (code formatting)
- PHPUnit 11.5+ (testing)

---

## 📖 Quick Reference

### Current Features

**✅ Production Ready:**
- Multi-step booking wizard (4 steps)
- Automatic staff assignment
- Email notifications (18 templates, PL/EN)
- Vehicle management system
- Google Maps location capture
- Settings system (Filament admin)
- Queue-based processing (Redis + Horizon)
- Role-based access control (4 roles)
- Business hours enforcement (9 AM - 6 PM)
- 24-hour advance booking requirement
- 24-hour cancellation policy

**⚠️ Known Issues (Fixed):**
- Test Send button parameter order (✅ Fixed Nov 2025)
- Preview button disabled (Livewire bug - use Test Send instead)
- Duplicate settings migration (✅ Deleted Nov 2025)

---

## 🚀 Getting Started

### For New Developers

1. **Read [CLAUDE.md](../CLAUDE.md)** - Essential commands, Docker setup
2. **Review [Project Map](./project_map.md)** - System architecture
3. **Explore feature docs** in `features/` folder
4. **Check ADRs** in `decisions/` for architectural context

### For Frontend Developers

1. **API Integration:** See [Project Map - API Endpoints](./project_map.md)
2. **Data Formats:** Check feature docs for data structures
3. **Authentication:** Session-based (CSRF tokens required)

### For Backend Developers

1. **Architecture:** [Project Map](./project_map.md)
2. **Patterns:** ADRs in `decisions/` + `decision_log/`
3. **Business Logic:** Service layer (see ADR-001)
4. **Testing:** Write feature tests for all new endpoints

---

## 🤝 Contributing to Docs

**When adding new feature:**
1. Create folder in `features/`
2. Add `README.md` with overview
3. Link from this hub (docs/README.md)
4. Update [CLAUDE.md](../CLAUDE.md) with quick reference
5. Create ADR in `decisions/` if architectural decision made

**Documentation Guidelines:**
- Keep files < 500 lines (split if larger)
- Use relative links (`./file.md`, `../folder/file.md`)
- Add "See Also" sections for cross-references
- Include code examples with syntax highlighting
- Update navigation hub when adding new docs

---

## 📞 Support

### Documentation Issues
- Check related ADR files for context
- Review [Project Map](./project_map.md) for detailed specs
- Search feature docs in `features/` folder

### Implementation Questions
- **Architecture:** See ADR-001 (Service Layer)
- **Availability System:** See ADR-002 (Time Slots)
- **Authorization:** See ADR-003 (RBAC)
- **Staff Assignment:** See ADR-004
- **Business Hours:** See ADR-005
- **Email System:** See `features/email-system/`

---

## 🔗 Useful Links

- **Laravel 12 Docs:** https://laravel.com/docs/12.x
- **Laravel Filament:** https://filamentphp.com/docs/4.x
- **Spatie Permission:** https://spatie.be/docs/laravel-permission/
- **Tailwind CSS 4.0:** https://tailwindcss.com/docs
- **Guava Calendar:** https://github.com/guava/calendar
- **Google Maps JS API:** https://developers.google.com/maps/documentation/javascript

---

## 🔬 Edge Cases Analysis

Detailed analysis of complex booking scenarios:

- **[Availability Gaps](./edge-cases/availability-gaps.md)** - Handling schedule discontinuities
- **[Multi-Day Services](./edge-cases/multi-day-services.md)** - Services spanning multiple days
- **[Race Conditions](./edge-cases/race-conditions.md)** - Concurrent booking prevention
- **[Timezone Handling](./edge-cases/timezone-handling.md)** - Time zone edge cases

---

## 📚 Advanced Topics

Additional technical documentation:

- **[API Contract](./api-contract-frontend.md)** - Complete API specification for frontend
- **[Backend Recommendations](./backend-recommendations.md)** - Architecture best practices
- **[Vehicle Pricing Logic](./features/vehicle-pricing-logic.md)** - Price calculation details
- **[Settings Manager](./features/settings-manager.md)** - Advanced configuration patterns

---

## 📅 Version History

### v2.1 (2025-11-26) - Documentation Audit & Update
- **Filament upgrade:** v3.3.42 → v4.2.3 + Guava Calendar v2.0
- **SMS System:** Added to documentation hub (production feature)
- **Edge Cases:** Added section with links to analysis docs
- **Advanced Topics:** Added section with API contract, backend recommendations
- **Fixed:** Filament docs link (3.x → 4.x), Guava Calendar version
- **Updated:** All "Last Updated" dates to 2025-11-26

### v2.0 (2025-11-09) - Documentation Restructuring
- **Major reorganization:** CLAUDE.md (1,863 lines → ~400 lines)
- **Granular docs:** Created `features/` with 5 feature folders
- **Email System:** 7 detailed documentation files
- **Navigation hub:** This file (docs/README.md) updated
- **Email fixes:** Test Send parameter order fixed, duplicate migration deleted
- **Archive:** Old temporary docs moved to `archive/`

### v1.1.1 (2025-10-18) - Bug Fixes & Refinements
- Fixed "staff_id required" error
- Added `findFirstAvailableStaff()` method
- Updated ADR-004 with implementation notes

### v1.1 (2025-10-18) - Booking System Enhancement
- Added ADR-004 (Automatic Staff Assignment)
- Added ADR-005 (Business Hours Configuration)
- Added 4 edge case documentations
- Configuration system implemented

### v1.0 (2025-10-12) - Initial Documentation
- Architecture analysis complete
- API contract defined
- ADRs created for key decisions (001-003)

---

**Current Version:** v2.0 (2025-11-09)
**Maintained by:** Development Team
**License:** Proprietary
