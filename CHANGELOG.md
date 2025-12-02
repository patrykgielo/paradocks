# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- (empty - ready for next changes)

## [0.3.1] - 2025-12-03

### Changed
- **BREAKING (Internal):** Converted email/SMS seeders to data migrations (Laravel best practice)
  - EmailTemplateSeeder → database/migrations/2025_12_02_224732_seed_email_templates.php (30 templates)
  - SmsTemplateSeeder → database/migrations/2025_12_02_225216_seed_sms_templates.php (14 templates)
  - Seeders now development/testing ONLY, data migrations for production
  - Production deployments: `php artisan migrate --force` (automatic, tracked, idempotent)
- Removed DeploySeederCommand (over-engineered, no longer needed)
- Updated DatabaseSeeder to call only dev/test seeders (Settings, Roles, Vehicle Types, Services)
- Simplified deployment workflow (removed manual seeder execution step)

### Added
- Comprehensive data migrations pattern guide (docs/guides/data-migrations.md)
- Data migration template section in FEATURE_TEMPLATE.md

### Fixed
- v0.3.0 deployment issue: email templates now properly seeded in production via migrations
- Missing 12 email templates will be added automatically on deployment (18→30)
- SMS templates will be seeded for the first time (0→14)

### Documentation
- Updated quick-start.md with data migrations pattern
- Updated CLAUDE.md deployment instructions
- Updated ci-cd-deployment.md runbook (removed seeder step)
- Updated FEATURE_TEMPLATE.md with seeder vs data migration guidance

## [0.3.0] - 2025-12-02

### Added
- Admin-created user welcome email notification system
  - Secure password setup flow with 24-hour token expiration
  - Dedicated email templates for admin-created users (PL/EN)
  - Password setup form with token validation
  - Event-driven architecture (AdminCreatedUser event + notification)
  - Integration with existing EmailService
- Security audit specialist agent for OWASP + GDPR compliance
- Comprehensive Git workflow documentation (Gitflow with staging-based release approval)
- CONTRIBUTING.md for contributor guidelines
- Complete Git workflow guide at docs/deployment/GIT_WORKFLOW.md

### Changed
- Filament UserResource password field made optional for admin user creation
- User model extended with password setup token methods
- Updated EmailTemplateSeeder with 2 new templates (30 total)

### Security
- Password setup tokens use 256-bit entropy (Str::random(64))
- Rate limiting on password setup endpoint (6 attempts/minute)
- Token expiration enforced at 24 hours

## [0.2.11] - 2025-11-30

### Fixed
- Health check port and endpoint configuration in CI/CD
- Docker container healthcheck configuration

### Changed
- Updated deployment workflow to use proper healthcheck strategy
- Improved zero-downtime deployment process

### Documentation
- Added comprehensive deployment history (v0.2.1-v0.2.11)
- Added environment variables reference guide
- Added known issues and troubleshooting guide
- Added complete dependencies inventory

## [0.2.10] - 2025-11-29

### Fixed
- Missing APP_KEY configuration in environment
- Healthcheck endpoint configuration

### Security
- Fixed environment variable exposure in Docker containers
- Properly secured sensitive configuration values

### Changed
- Updated Docker environment variable handling
- Improved environment configuration validation

## [0.2.9] - 2025-11-28

### Added
- Zero-downtime healthcheck deployment strategy (ADR-011)
- Container health monitoring during deployment
- Graceful failover mechanism

### Changed
- Deployment workflow migrated from maintenance mode to healthcheck-based
- Improved deployment reliability and uptime

### Removed
- Old maintenance mode deployment approach (replaced with healthcheck)

### Documentation
- Added ADR-011: Healthcheck Deployment Strategy
- Updated deployment runbooks

## [0.2.8] - 2025-11-27

### Fixed
- Docker environment variable configuration issues
- Container startup failures due to missing env vars

### Changed
- Improved docker-compose.yml environment section
- Enhanced environment variable validation

## [0.2.7] - 2025-11-26

### Fixed
- Environment variable hierarchy in Docker
- Configuration loading in containerized environment

### Documentation
- Added critical deployment knowledge documentation
- Documented Docker env var patterns and gotchas

## [0.2.6] - 2025-11-25

### Fixed
- Container build failures
- Permission issues with storage directory

### Changed
- Improved Docker build process
- Enhanced permission handling in containers

## [0.2.5] - 2025-11-24

### Fixed
- Deployment workflow configuration
- GitHub Actions environment setup

### Changed
- Updated CI/CD pipeline configuration
- Improved deployment error handling

## [0.2.0] - 2025-11-20

### Added
- Customer profile management system with 5 subpages
  - Personal information (name, phone)
  - Vehicle management (type, brand, model, year)
  - Address management with Google Maps autocomplete
  - Notification preferences (SMS, email, marketing)
  - Security settings (password change, email change, account deletion)
- Staff scheduling system with calendar integration
  - Base weekly schedules with effective date ranges
  - Single-day exceptions (sick days, extra work days)
  - Vacation period management with approval workflow
  - Service-staff assignments via pivot table
  - Simplified UX navigation (2 main sections: Harmonogramy, Urlopy)
- CMS system with 4 content types
  - Pages: Static content with layout options
  - Posts: Blog/news with categories
  - Promotions: Offers with validity dates
  - Portfolio: Project showcases with before/after images
- Content Builder blocks (image, gallery, video, CTA, columns, quotes)

### Changed
- User model refactored to use first_name/last_name fields
- Improved address storage with Google Maps place_id
- Enhanced booking wizard with vehicle integration
- Simplified staff scheduling navigation (4 → 2 main sections)

### Fixed
- User model mass assignment vulnerability
- Session encryption configuration
- Booking wizard mobile responsiveness
- Staff availability edge cases (vacation priority logic)

### Documentation
- Added customer profile feature documentation
- Added staff scheduling guide
- Added CMS system documentation
- Added UX migration decision (ADR: UX-MIGRATION-001)

## [0.1.0] - 2025-11-01

### Added
- Initial Laravel 12.32.5 setup
- Docker Compose configuration (9 services)
- Filament v4.2.3 admin panel
- Basic booking system with multi-step wizard
  - Service selection
  - Date/time selection
  - Vehicle information capture
  - Location capture with Google Maps
- Email system with transactional templates
  - 18 templates (9 types × 2 languages)
  - Queue-based delivery with Redis
  - Email event tracking and logging
- Vehicle management system
  - Vehicle types, brands, models
  - Customer vehicle declarations
- Google Maps integration
  - Places Autocomplete API
  - Location storage (address, lat/lng, place_id)
- Settings system with Filament admin interface
  - Booking settings
  - Map configuration
  - Contact information
  - Marketing settings
- Maintenance mode system
  - 4 maintenance types (deployment, pre-launch, scheduled, emergency)
  - Redis-based state management
  - Role-based and token-based bypass
  - Filament admin UI

### Infrastructure
- Docker containers: app, nginx, mysql, node, redis, queue, horizon, scheduler, mailpit
- Vite 7+ for asset bundling
- Tailwind CSS 4.0 with @tailwindcss/vite plugin
- MySQL 8.0 database
- Redis for queue and cache
- Laravel Horizon for queue monitoring
- Nginx reverse proxy with SSL

### Security
- Spatie Laravel Permission (4 roles: super-admin, admin, staff, customer)
- CSRF protection enabled
- Session encryption configured
- Environment-based security settings

### Documentation
- Complete project documentation in app/docs/
- Quick start guide
- Docker guide
- Troubleshooting guide
- Feature-specific documentation
- Database schema documentation
- Architecture Decision Records (ADRs)

---

## Version History Legend

**Version Types**:
- **[Unreleased]**: Changes not yet released
- **[X.Y.Z]**: Released version with date

**Change Categories**:
- **Added**: New features
- **Changed**: Changes in existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Now removed features
- **Fixed**: Bug fixes
- **Security**: Security vulnerability fixes
- **Documentation**: Documentation changes only

**Semantic Versioning**:
- **MAJOR** (X.0.0): Breaking changes or first production release
- **MINOR** (0.X.0): New features (backward-compatible)
- **PATCH** (0.0.X): Bug fixes (backward-compatible)

---

## Release Process

**See**: [Git Workflow Guide](docs/deployment/GIT_WORKFLOW.md)

**Quick Reference**:
1. Feature merged to `develop`
2. Deploy to `staging` (auto)
3. QA testing on staging
4. Create `release/vX.Y.Z` branch
5. Update this CHANGELOG.md
6. Merge to `main` + tag
7. Deploy to production (auto)

**Tagging**:
```bash
# After merging release to main
git tag -a v0.3.0 -m "Release v0.3.0 - [Feature Name]"
git push origin v0.3.0
```

---

## Unreleased Changes Tracking

**How to track unreleased changes**:

1. **During development**: Add changes to `[Unreleased]` section
2. **When releasing**: Move `[Unreleased]` to new version section
3. **Add release date**: `## [X.Y.Z] - YYYY-MM-DD`

**Example workflow**:
```markdown
## [Unreleased]

### Added
- New customer profile feature

## [0.2.11] - 2025-11-30
...
```

**After release**:
```markdown
## [Unreleased]

### Added
- (empty - ready for next changes)

## [0.3.0] - 2025-12-01

### Added
- New customer profile feature

## [0.2.11] - 2025-11-30
...
```

---

## Contributing

When contributing, please update this CHANGELOG.md:

1. **Add changes** to `[Unreleased]` section
2. **Use appropriate category**: Added, Changed, Fixed, etc.
3. **Be descriptive**: Explain what changed and why
4. **Link issues**: Reference GitHub issues if applicable

**Example**:
```markdown
## [Unreleased]

### Added
- Customer loyalty program with point tracking (#123)
- Automated birthday email notifications (#124)

### Fixed
- Booking wizard validation error on mobile (#125)
```

---

**Last Updated**: 2025-12-01
**Maintained By**: Paradocks Development Team
