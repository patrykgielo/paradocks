# Paradocks - Documentation Hub

**Last Updated:** November 9, 2025

Centralna nawigacja po dokumentacji projektu Laravel 12 + Filament 3.3.

## 🚀 Quick Start

**Nowy developer?** Zobacz:
1. **[CLAUDE.md](../CLAUDE.md)** - Quick reference + essential commands
2. **[Project Map](./project_map.md)** - High-level system overview

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

## 🏗️ Architecture

- **[Project Map](./project_map.md)** - Complete system overview, domain model, relationships

**Coming Soon:**
- `architecture/overview.md` - High-level architecture
- `architecture/database-schema.md` - ERD, tables, indexes
- `architecture/queue-system.md` - Redis + Horizon

---

## 📝 Decisions (ADRs)

Architecture Decision Records documenting major technical choices:

**New ADRs** (October/November 2025):
- **[ADR-004: Automatic Staff Assignment](./decisions/ADR-004-automatic-staff-assignment.md)** - Removed manual staff selection
- **[ADR-005: Business Hours Configuration](./decisions/ADR-005-business-hours-config.md)** - Centralized booking rules

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
- Laravel Filament v3.3+
- Spatie Laravel Permission v6.21
- Guava Calendar v1.14.2

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
- **Laravel Filament:** https://filamentphp.com/docs/3.x
- **Spatie Permission:** https://spatie.be/docs/laravel-permission/
- **Tailwind CSS 4.0:** https://tailwindcss.com/docs
- **Guava Calendar:** https://github.com/guava/calendar
- **Google Maps JS API:** https://developers.google.com/maps/documentation/javascript

---

## 📅 Version History

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
