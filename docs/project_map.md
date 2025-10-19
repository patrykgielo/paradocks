# Project Map - Paradocks Application

**Last Updated:** 2025-10-19
**Laravel Version:** 12.x
**PHP Version:** 8.2+

## Project Overview

Paradocks is a booking and appointment management system built with Laravel 12, Filament v3.3+ admin panel, and modern frontend stack (Vite + Alpine.js + Tailwind CSS 4.0). The application focuses on service booking workflow with role-based access control.

## Recent Changes (2025-10-19)

### Staff Role Enforcement (ADR-006)
Implemented comprehensive validation system to ensure only users with 'staff' role can be assigned to appointments.

**Key Components:**
- `AppointmentObserver` - Model-level validation on create/update
- `StaffRoleRule` - Custom validation rule for controllers
- Data cleanup commands: `appointments:audit-staff`, `appointments:fix-staff`
- 5-layer defense-in-depth strategy

**Affected Files:**
- Services: `AppointmentService.php` (3 query fixes)
- Seeders: `ServiceAvailabilitySeeder.php`
- Controllers: `AppointmentController.php`
- Filament: `AppointmentResource.php`, Create/Edit pages
- Tests: Feature + Unit test coverage

See `/docs/decisions/ADR-006-staff-role-enforcement.md` for details.

## Directory Structure

```
app/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── AuditInvalidStaffAssignments.php  # NEW: Data cleanup
│   │       └── FixInvalidStaffAssignments.php    # NEW: Data cleanup
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── UserResource.php           # All users management
│   │   │   ├── CustomerResource.php       # Customer-only view
│   │   │   ├── EmployeeResource.php       # Staff-only view
│   │   │   ├── ServiceResource.php
│   │   │   ├── AppointmentResource.php
│   │   │   └── RoleResource.php
│   │   └── Pages/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AppointmentController.php  # Booking flow + validation
│   │   │   └── HomeController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php                       # Extended with profile fields
│   │   ├── Service.php
│   │   ├── Appointment.php
│   │   └── ServiceAvailability.php
│   ├── Observers/
│   │   └── AppointmentObserver.php        # NEW: Staff role validation
│   ├── Rules/
│   │   └── StaffRoleRule.php              # NEW: Custom validation rule
│   ├── Services/
│   │   └── AppointmentService.php         # Business logic for booking
│   └── Providers/
│       ├── Filament/AdminPanelProvider.php
│       └── AppServiceProvider.php         # UPDATED: Observer registration
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 2025_10_06_190313_create_permission_tables.php
│   │   ├── 2025_10_06_190501_create_services_table.php
│   │   ├── 2025_10_06_190502_create_service_availabilities_table.php
│   │   ├── 2025_10_06_190503_create_appointments_table.php
│   │   └── 2025_10_18_122658_extend_users_profile_fields.php  # NEW
│   ├── factories/
│   │   └── UserFactory.php                # Updated with profile fields
│   └── seeders/
│       └── DatabaseSeeder.php             # Updated with test data
├── resources/
│   ├── views/
│   │   ├── booking/
│   │   │   └── create.blade.php           # Multi-step booking wizard
│   │   ├── appointments/
│   │   │   └── index.blade.php
│   │   ├── layouts/
│   │   │   └── app.blade.php
│   │   └── welcome.blade.php
│   ├── js/
│   │   ├── app.js                         # Alpine.js components + validation
│   │   └── bootstrap.js
│   └── css/
│       └── app.css                        # Tailwind CSS
├── routes/
│   ├── web.php                            # Public + auth routes
│   └── api.php                            # Available slots API
├── tests/
│   ├── Feature/
│   │   └── AppointmentStaffValidationTest.php  # NEW: Staff role tests
│   └── Unit/
│       └── Services/
│           └── AppointmentServiceTest.php      # NEW: Service tests
└── docs/
    ├── decisions/
    │   ├── ADR-001-extended-user-profile-fields.md
    │   └── ADR-006-staff-role-enforcement.md   # NEW
    └── project_map.md                          # This file
```

## Core Modules

### 1. User Management

**Key Files:**
- `app/Models/User.php`
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/CustomerResource.php`
- `app/Filament/Resources/EmployeeResource.php`

**Responsibilities:**
- User authentication (Laravel Breeze)
- Role-based access control (Spatie Permissions)
- Profile management with extended fields (first_name, last_name, phone, address)
- Integration with Filament admin panel

**Database Tables:**
- `users` - Core user data + profile fields
- `roles` - User roles (super-admin, admin, staff, customer)
- `permissions` - Fine-grained permissions
- `model_has_roles` - Role assignments

**Recent Changes:**
- **2025-10-18**: Extended user profile with 8 new fields (see ADR-001)
- Name field split into first_name/last_name with data migration
- Added phone_e164 (E.164 format) and Polish address fields

### 2. Service Management

**Key Files:**
- `app/Models/Service.php`
- `app/Models/ServiceAvailability.php`
- `app/Filament/Resources/ServiceResource.php`

**Responsibilities:**
- Service catalog (detailing services)
- Pricing and duration configuration
- Staff-service assignments
- Availability scheduling per service/staff

**Database Tables:**
- `services` - Service definitions
- `service_availabilities` - Staff availability slots

### 3. Appointment Booking System

**Key Files:**
- `app/Models/Appointment.php`
- `app/Http/Controllers/AppointmentController.php`
- `app/Services/AppointmentService.php`
- `resources/views/booking/create.blade.php`
- `resources/js/app.js` (bookingWizard Alpine component)

**Booking Flow (4 Steps):**
1. **Service Selection** - Choose from available services
2. **Date & Time** - Calendar + available slots (24h advance booking)
3. **Customer Details** - Contact info + address (NEW: extended fields)
4. **Summary & Confirmation** - Review and submit

**Responsibilities:**
- Multi-step booking wizard (Alpine.js)
- Automatic staff assignment based on availability
- Slot availability calculation
- Email notifications (planned)
- User profile auto-update during booking

**Database Tables:**
- `appointments` - Booking records
- `status` enum: pending, confirmed, cancelled, completed

**Recent Changes:**
- **2025-10-18**: Step 3 now collects full contact/address data
- Added frontend validation (phone E.164, postal code mask)
- Controller updates user profile on booking submission

### 4. Admin Panel (Filament)

**Key Files:**
- `app/Providers/Filament/AdminPanelProvider.php`
- All `app/Filament/Resources/*Resource.php`

**Access URL:** `/admin`
**Access Control:** Users with roles: super-admin, admin, staff

**Features:**
- User/Customer/Employee management
- Service CRUD
- Appointment dashboard
- Role & Permission management
- Polish localization

**Recent Changes:**
- **2025-10-18**: All User resources updated with new profile fields
- Forms reorganized into logical sections (Personal, Contact, Address)
- Tables now display first_name + last_name columns
- Added postal code input mask (99-999)

## Database Schema

### Users Table (Extended)

| Column          | Type         | Nullable | Description                          |
|-----------------|--------------|----------|--------------------------------------|
| id              | bigint       | No       | Primary key                          |
| name            | varchar(255) | No       | Legacy field (accessor only)         |
| first_name      | varchar(255) | Yes      | User's first name                    |
| last_name       | varchar(255) | Yes      | User's last name                     |
| email           | varchar(255) | No       | Unique email                         |
| email_verified_at | timestamp  | Yes      | Verification timestamp               |
| phone_e164      | varchar(20)  | Yes      | Phone in E.164 format                |
| street_name     | varchar(255) | Yes      | Street name                          |
| street_number   | varchar(20)  | Yes      | Building/apt number                  |
| city            | varchar(255) | Yes      | City                                 |
| postal_code     | varchar(10)  | Yes      | Postal code (XX-XXX)                 |
| access_notes    | text         | Yes      | Address access information           |
| password        | varchar(255) | No       | Hashed password                      |
| remember_token  | varchar(100) | Yes      | Session token                        |
| created_at      | timestamp    | Yes      | Record creation                      |
| updated_at      | timestamp    | Yes      | Last update                          |

### Services Table

| Column             | Type         | Nullable | Description                   |
|--------------------|--------------|----------|-------------------------------|
| id                 | bigint       | No       | Primary key                   |
| name               | varchar(255) | No       | Service name                  |
| description        | text         | Yes      | Service description           |
| duration_minutes   | int          | No       | Service duration              |
| price              | decimal(10,2)| No       | Service price                 |
| is_active          | boolean      | No       | Active status                 |
| created_at         | timestamp    | Yes      | Record creation               |
| updated_at         | timestamp    | Yes      | Last update                   |

### Appointments Table

| Column             | Type         | Nullable | Description                   |
|--------------------|--------------|----------|-------------------------------|
| id                 | bigint       | No       | Primary key                   |
| service_id         | bigint       | No       | FK to services                |
| customer_id        | bigint       | No       | FK to users (customer)        |
| staff_id           | bigint       | No       | FK to users (staff)           |
| appointment_date   | date         | No       | Appointment date              |
| start_time         | time         | No       | Start time                    |
| end_time           | time         | No       | End time                      |
| status             | enum         | No       | pending/confirmed/cancelled/completed |
| notes              | text         | Yes      | Customer notes                |
| cancellation_reason| text         | Yes      | Reason for cancellation       |
| created_at         | timestamp    | Yes      | Record creation               |
| updated_at         | timestamp    | Yes      | Last update                   |

## Frontend Architecture

### Technology Stack
- **Build Tool:** Vite 7.x
- **CSS Framework:** Tailwind CSS 4.0
- **JavaScript Framework:** Alpine.js
- **HTTP Requests:** Fetch API

### Alpine.js Components

**bookingWizard** (`resources/js/app.js`)
- Multi-step form state management
- Step validation (including new profile validation)
- Available slots fetching
- Progress tracking

**Recent Changes:**
- **2025-10-18**: Added `validateStep3()` method
- Customer object extended with all profile fields
- E.164 phone validation: `/^\+\d{1,3}\d{6,14}$/`
- Polish postal code validation: `/^\d{2}-\d{3}$/`

### API Endpoints

| Endpoint              | Method | Description                    | Auth Required |
|-----------------------|--------|--------------------------------|---------------|
| `/api/available-slots`| POST   | Get available booking slots    | No            |
| `/appointments`       | POST   | Create new appointment         | Yes           |
| `/appointments/{id}/cancel` | POST | Cancel appointment      | Yes           |

## Configuration

### Environment Variables

**Database (Docker):**
```env
DB_CONNECTION=mysql
DB_HOST=paradocks-mysql
DB_PORT=3306
DB_DATABASE=paradocks
DB_USERNAME=paradocks
DB_PASSWORD=password
```

**Queue (Sync by default):**
```env
QUEUE_CONNECTION=sync
```

**Mail (for notifications):**
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

## Development Workflow

### Local Development (Docker)

```bash
# Start services
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate

# Build frontend
docker compose exec node npm run build

# Watch mode
docker compose exec node npm run dev
```

### Direct Development

```bash
cd app
composer run dev  # Starts: server + queue + pail + vite
```

## Testing Strategy

### Manual Testing Checklist
- [ ] User registration/login
- [ ] Booking flow (all 4 steps)
- [ ] Profile data validation
- [ ] Admin panel CRUD operations
- [ ] Role-based access control

### Automated Testing
- Unit tests: `tests/Unit/`
- Feature tests: `tests/Feature/`
- Run: `php artisan test`

## Recent Changes Log

### 2025-10-19: Staff Role Enforcement (ADR-006)

**Implementation:** Defense-in-depth validation strategy with 5 layers

**New Files:**
- `app/Observers/AppointmentObserver.php` - Model-level validation
- `app/Rules/StaffRoleRule.php` - Custom validation rule
- `app/Console/Commands/AuditInvalidStaffAssignments.php` - Audit command
- `app/Console/Commands/FixInvalidStaffAssignments.php` - Fix command
- `tests/Feature/AppointmentStaffValidationTest.php` - Feature tests
- `tests/Unit/Services/AppointmentServiceTest.php` - Unit tests
- `docs/decisions/ADR-006-staff-role-enforcement.md` - Documentation

**Modified Files:**
- `app/Services/AppointmentService.php` - Fixed 3 role queries to only use 'staff'
- `database/seeders/ServiceAvailabilitySeeder.php` - Fixed role query
- `app/Http/Controllers/AppointmentController.php` - Added StaffRoleRule validation
- `app/Filament/Resources/AppointmentResource.php` - Reverted to correct implementation
- `app/Filament/Resources/AppointmentResource/Pages/CreateAppointment.php` - Added validation
- `app/Filament/Resources/AppointmentResource/Pages/EditAppointment.php` - Added validation
- `app/Providers/AppServiceProvider.php` - Registered observer

**Data Cleanup Commands:**
```bash
# Audit existing appointments for invalid staff assignments
php artisan appointments:audit-staff

# Preview fixes (dry-run)
php artisan appointments:fix-staff --dry-run

# Apply fixes
php artisan appointments:fix-staff
```

**Business Rules:**
- ONLY users with 'staff' role can be assigned to appointments
- Admin and super-admin roles are blocked from appointment assignments
- Observer validates on every create/update operation
- Multiple validation layers ensure data integrity

### 2025-10-18: Extended User Profile Fields (ADR-001)

**Migration:** `2025_10_18_122658_extend_users_profile_fields.php`

**Changed Files:**
- `app/Models/User.php` - Added 8 fields to $fillable, name accessor
- `app/Filament/Resources/UserResource.php` - Form/table updates
- `app/Filament/Resources/CustomerResource.php` - Form/table updates
- `app/Filament/Resources/EmployeeResource.php` - Form/table updates
- `resources/views/booking/create.blade.php` - Step 3 expansion
- `resources/js/app.js` - Validation logic
- `app/Http/Controllers/AppointmentController.php` - Validation + profile update
- `database/factories/UserFactory.php` - Faker data
- `database/seeders/DatabaseSeeder.php` - Test data

**New Fields:**
- first_name, last_name (personal data)
- phone_e164 (E.164 international format)
- street_name, street_number, city, postal_code, access_notes (address)

**Backward Compatibility:**
- Original `name` field retained
- Model accessor returns `first_name + last_name`
- Existing users auto-migrated with name split

## Dependencies

### Core PHP Packages
- Laravel 12.x
- Filament 3.3+
- Spatie Laravel Permission 6.21+
- Guava Calendar 1.14.2

### Frontend Packages
- Alpine.js 3.x
- Tailwind CSS 4.0
- Vite 7.x

## Access Control

### Roles
- **super-admin**: Full system access
- **admin**: Administrative access
- **staff**: Can manage appointments, view customers
- **customer**: Can book appointments, view own bookings

### Filament Access
```php
// app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    return $this->hasAnyRole(['super-admin', 'admin', 'staff']);
}
```

## Known Issues & Technical Debt

1. **Name Migration Edge Cases**: Multiple middle names may not split correctly
2. **Legacy Code**: Some views still reference `$user->name` directly (works via accessor)
3. **Phone Validation**: Currently Polish-focused (+48), needs internationalization
4. **Address Autocomplete**: Not implemented (Google Maps API integration planned)

## Future Roadmap

1. **SMS Notifications**: Use phone_e164 for appointment reminders
2. **Multiple Addresses**: Allow users to save multiple delivery locations
3. **Profile Completion Tracking**: Encourage users to complete profiles
4. **International Phone Support**: Expand beyond Polish market
5. **Address Geolocation**: Map integration for mobile services
6. **Calendar Integration**: Google Calendar, iCal export

## Documentation Standards

### Architecture Decision Records (ADR)
- Location: `docs/decisions/ADR-XXX-title.md`
- Format: Problem → Options → Decision → Consequences
- Review: Quarterly or on major changes

### Code Documentation
- PHPDoc for all public methods
- Inline comments for complex logic
- Blade comments for UI sections

## Support & Resources

- **Laravel Docs**: https://laravel.com/docs
- **Filament Docs**: https://filamentphp.com/docs
- **Alpine.js Docs**: https://alpinejs.dev
- **Tailwind CSS**: https://tailwindcss.com/docs

---

**Maintained by:** Development Team
**Next Review:** 2025-11-18
