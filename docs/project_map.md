# Project Map - Paradocks Booking System

**Last Updated:** 2025-10-31
**Laravel Version:** 12
**PHP Version:** 8.2+
**Database:** SQLite (development), MySQL (Docker production)

## Architecture Pattern

**Pattern:** MVC with Service Layer Architecture

The application follows a traditional **Model-View-Controller (MVC)** pattern enhanced with a dedicated **Service Layer** for complex business logic. This approach keeps controllers thin, models focused on data relationships, and business logic isolated in reusable service classes.

**Key Architectural Decisions:**
- **Thin Controllers:** Controllers handle HTTP concerns (request/response, validation)
- **Service Layer:** Complex business logic extracted to `AppointmentService`
- **Rich Models:** Models use query scopes and accessors for data retrieval patterns
- **Filament Admin:** Complete admin panel using Laravel Filament v3.3+
- **Role-Based Access Control:** Spatie Laravel Permission package

## Core Models

### User
**Location:** `/var/www/projects/paradocks/app/app/Models/User.php`

**Purpose:** Authentication and authorization for customers, staff, and administrators

**Relationships:**
- `staffAppointments()` → hasMany(Appointment) - Appointments where user is staff
- `customerAppointments()` → hasMany(Appointment) - Appointments where user is customer
- `serviceAvailabilities()` → hasMany(ServiceAvailability) - Staff availability schedule
- Uses Spatie `HasRoles` trait for role management

**Key Methods:**
- `canAccessPanel(Panel $panel): bool` - Filament admin access control
- `isCustomer(): bool` - Role checking helper
- `isStaff(): bool` - Role checking helper
- `isAdmin(): bool` - Role checking helper

**Fillable Attributes:** name, email, password

**Casts:** email_verified_at (datetime), password (hashed)

**Roles Defined:**
- `super-admin` - Full system access
- `admin` - Administrative access
- `staff` - Service provider access
- `customer` - Customer booking access

**Notes:**
- Implements `FilamentUser` interface for admin panel access
- Admin panel accessible only to super-admin, admin, and staff roles
- No phone number or address fields (potential gap for customer data)

---

### Service
**Location:** `/var/www/projects/paradocks/app/app/Models/Service.php`

**Purpose:** Represents detailing services offered (e.g., Basic Wash, Premium Detail, Ceramic Coating)

**Relationships:**
- `appointments()` → hasMany(Appointment)
- `serviceAvailabilities()` → hasMany(ServiceAvailability)

**Key Scopes:**
- `scopeActive($query)` - Filter active services only
- `scopeOrdered($query)` - Order by sort_order field

**Fillable Attributes:**
- `name` (string) - Service name
- `description` (text, nullable) - Service description
- `duration_minutes` (integer) - Expected service duration
- `price` (decimal) - Base price
- `is_active` (boolean) - Whether service is currently offered
- `sort_order` (integer) - Display order

**Casts:**
- `is_active` → boolean
- `price` → decimal:2

**Notes:**
- **MISSING:** No support for service tiers (Basic/Premium/Elite)
- **MISSING:** No add-ons or package system
- **MISSING:** No image/gallery support for service visualization
- **MISSING:** No category or service type classification

---

### Appointment
**Location:** `/var/www/projects/paradocks/app/app/Models/Appointment.php`

**Purpose:** Represents a booked appointment/visit

**Relationships:**
- `service()` → belongsTo(Service)
- `customer()` → belongsTo(User, 'customer_id')
- `staff()` → belongsTo(User, 'staff_id')

**Key Scopes:**
- `scopeStatus($query, string $status)` - Filter by status
- `scopePending($query)` - Pending appointments
- `scopeConfirmed($query)` - Confirmed appointments
- `scopeCancelled($query)` - Cancelled appointments
- `scopeCompleted($query)` - Completed appointments
- `scopeUpcoming($query)` - Future pending/confirmed appointments
- `scopeForCustomer($query, int $customerId)` - Customer's appointments
- `scopeForStaff($query, int $staffId)` - Staff member's appointments
- `scopeDateRange($query, string $startDate, string $endDate)` - Date range filter

**Fillable Attributes:**
- `service_id` (foreignId)
- `customer_id` (foreignId)
- `staff_id` (foreignId)
- `appointment_date` (date)
- `start_time` (time)
- `end_time` (time)
- `status` (enum: pending, confirmed, cancelled, completed)
- `notes` (text, nullable)
- `cancellation_reason` (text, nullable)

**Casts:**
- `appointment_date` → date
- `start_time` → datetime:H:i
- `end_time` → datetime:H:i

**Computed Attributes:**
- `is_upcoming` - Whether appointment is in the future
- `is_past` - Whether appointment is in the past
- `can_be_cancelled` - Whether appointment can be cancelled

**Database Indexes:**
- Composite: (appointment_date, start_time)
- Composite: (staff_id, appointment_date)
- Composite: (customer_id, appointment_date)

**Notes:**
- ✅ **IMPLEMENTED:** Vehicle information (type, brand, model, year) via VehicleType/CarBrand/CarModel
- ✅ **IMPLEMENTED:** Google Maps location data (address, coordinates, place_id)
- **MISSING:** No pricing breakdown (base + add-ons)
- **MISSING:** No deposit/payment tracking
- **MISSING:** No reminder system metadata

---

### ServiceAvailability
**Location:** `/var/www/projects/paradocks/app/app/Models/ServiceAvailability.php`

**Purpose:** Defines when staff members are available to perform specific services

**Relationships:**
- `service()` → belongsTo(Service)
- `user()` → belongsTo(User) - Staff member

**Key Scopes:**
- `scopeForDay($query, int $dayOfWeek)` - Filter by day of week
- `scopeForUser($query, int $userId)` - Filter by staff member
- `scopeForService($query, int $serviceId)` - Filter by service

**Fillable Attributes:**
- `service_id` (foreignId)
- `user_id` (foreignId) - Staff member
- `day_of_week` (integer) - 0=Sunday, 6=Saturday
- `start_time` (time)
- `end_time` (time)

**Casts:**
- `day_of_week` → integer
- `start_time` → datetime:H:i
- `end_time` → datetime:H:i

**Database Constraints:**
- Unique constraint: (user_id, service_id, day_of_week, start_time) - Prevents overlapping availability

**Notes:**
- Supports recurring weekly availability only
- **MISSING:** No support for one-time exceptions (holidays, vacation days)
- **MISSING:** No buffer time between appointments
- **MISSING:** No capacity management (multiple staff same time)

---

### VehicleType
**Location:** `/var/www/projects/paradocks/app/app/Models/VehicleType.php`

**Purpose:** Categorizes vehicles into types (city car, delivery van, etc.) for service planning

**Relationships:**
- `carModels()` → belongsToMany(CarModel) via `vehicle_type_car_model` pivot
- `appointments()` → hasMany(Appointment)

**Key Scopes:**
- `scopeActive($query)` - Filter active types only
- `scopeOrdered($query)` - Order by sort_order field

**Fillable Attributes:**
- `name` (string) - Display name (e.g., "Auto miejskie")
- `slug` (string, unique) - URL-friendly identifier (e.g., "city_car")
- `description` (text, nullable) - Type description
- `examples` (string, nullable) - Example vehicles
- `sort_order` (integer) - Display order
- `is_active` (boolean) - Whether type is active

**Casts:**
- `is_active` → boolean
- `sort_order` → integer

**Seeded Data:** 5 types (city_car, small_car, medium_car, large_car, delivery_van)

**Notes:**
- Seeded data only, not user-created
- Used for future pricing logic (vehicle type affects service price)

---

### CarBrand
**Location:** `/var/www/projects/paradocks/app/app/Models/CarBrand.php`

**Purpose:** Represents car manufacturers (Toyota, VW, Ford, etc.)

**Relationships:**
- `models()` → hasMany(CarModel)
- `appointments()` → hasMany(Appointment)

**Key Scopes:**
- `scopeActive($query)` - Active brands only
- `scopePending($query)` - Pending approval brands
- `scopeStatus($query, string $status)` - Filter by status

**Fillable Attributes:**
- `name` (string) - Brand name
- `slug` (string, unique) - URL-friendly identifier
- `status` (enum: pending, active, inactive) - Approval status

**Casts:**
- `status` → string

**Notes:**
- Status 'pending' for user-submitted brands awaiting admin approval
- Auto-slug generation in Filament form

---

### CarModel
**Location:** `/var/www/projects/paradocks/app/app/Models/CarModel.php`

**Purpose:** Represents specific car models (Corolla, Golf, Transit, etc.)

**Relationships:**
- `brand()` → belongsTo(CarBrand)
- `vehicleTypes()` → belongsToMany(VehicleType) via pivot
- `appointments()` → hasMany(Appointment)

**Key Scopes:**
- `scopeActive($query)` - Active models only
- `scopePending($query)` - Pending approval models
- `scopeForBrand($query, int $brandId)` - Models for specific brand
- `scopeForVehicleType($query, int $typeId)` - Models for specific vehicle type

**Computed Attributes:**
- `full_name` - Returns "Brand Model" (e.g., "Toyota Corolla")

**Fillable Attributes:**
- `car_brand_id` (foreignId)
- `name` (string) - Model name
- `slug` (string) - URL-friendly identifier (unique per brand)
- `year_from` (year, nullable) - First production year
- `year_to` (year, nullable) - Last production year
- `status` (enum: pending, active, inactive)

**Casts:**
- `year_from` → integer
- `year_to` → integer
- `status` → string

**Database Constraints:**
- Unique: (car_brand_id, slug) - Same slug allowed for different brands

**Notes:**
- Many-to-many with VehicleType (one model can belong to multiple types)
- Example: VW Golf can be "Auto małe" AND "Auto średnie"

---

## Controllers

### HomeController
**Location:** `/var/www/projects/paradocks/app/app/Http/Controllers/HomeController.php`

**Purpose:** Displays public home page with active services

**Routes:**
- `GET /` → `index()` (route name: 'home')

**Methods:**
- `index()` - Fetch and display active services ordered by sort_order

**Notes:**
- Very simple controller, no authentication required
- Only shows active services

---

### BookingController
**Location:** `/var/www/projects/paradocks/app/app/Http/Controllers/BookingController.php`

**Purpose:** Handle booking flow and availability checks

**Middleware:** `auth` - All routes require authentication

**Dependencies:**
- `AppointmentService` - Injected via constructor

**Routes:**
- `GET /services/{service}/book` → `create()` (route name: 'booking.create')
- `POST /api/available-slots` → `getAvailableSlots()` (route name: 'booking.slots')

**Methods:**

1. `create(Service $service)`
   - Display booking form for a specific service
   - Fetch staff members who have availability for the service
   - Returns view: `booking.create`

2. `getAvailableSlots(Request $request)`
   - **API Endpoint** - Returns JSON
   - Validates: service_id, staff_id, date
   - Returns available time slots for given date/service/staff
   - Uses `AppointmentService::getAvailableTimeSlots()`

**Notes:**
- Good separation of concerns with AppointmentService
- API endpoint for real-time slot checking exists
- **MISSING:** No guest booking capability (auth required)
- **MISSING:** No multi-step booking flow

---

### AppointmentController
**Location:** `/var/www/projects/paradocks/app/app/Http/Controllers/AppointmentController.php`

**Purpose:** Manage appointment lifecycle (create, view, cancel)

**Middleware:** `auth` - All routes require authentication

**Dependencies:**
- `AppointmentService` - Injected via constructor

**Routes:**
- `GET /my-appointments` → `index()` (route name: 'appointments.index')
- `POST /appointments` → `store()` (route name: 'appointments.store')
- `POST /appointments/{appointment}/cancel` → `cancel()` (route name: 'appointments.cancel')

**Methods:**

1. `index()`
   - Show customer's appointments
   - Loads with service and staff relationships
   - Ordered by appointment_date DESC, start_time DESC

2. `store(Request $request)`
   - Validates booking request
   - Calls `AppointmentService::validateAppointment()`
   - Creates appointment with status 'pending'
   - Redirects to appointments.index with success message

3. `cancel(Appointment $appointment)`
   - Authorizes: customer_id must match Auth::id()
   - Checks: `can_be_cancelled` attribute
   - Updates status to 'cancelled' with reason
   - Returns back with success message

**Validation Rules (store):**
- `service_id` - required, exists:services
- `staff_id` - required, exists:users
- `appointment_date` - required, date, after_or_equal:today
- `start_time` - required, H:i format
- `end_time` - required, H:i format, after:start_time
- `notes` - nullable, string, max:1000

**Notes:**
- Good authorization checking for cancellations
- Business logic properly delegated to service layer
- **MISSING:** No update/reschedule functionality
- **MISSING:** No payment integration
- **MISSING:** No email notifications

---

## Business Logic Layer

### AppointmentService
**Location:** `/var/www/projects/paradocks/app/app/Services/AppointmentService.php`

**Purpose:** Centralized business logic for appointment management and availability checking

**Key Methods:**

1. `checkStaffAvailability(int $staffId, int $serviceId, Carbon $date, Carbon $startTime, Carbon $endTime, ?int $excludeAppointmentId = null): bool`
   - Verifies staff has configured availability for the day/service
   - Checks for conflicting appointments (comprehensive overlap detection)
   - Returns true if slot is available

2. `getAvailableTimeSlots(int $serviceId, int $staffId, Carbon $date, int $serviceDurationMinutes): array`
   - Returns array of available time slots for given date
   - Slots are in 15-minute intervals
   - Each slot includes: start, end, datetime_start, datetime_end
   - Filters out unavailable slots using checkStaffAvailability()

3. `validateAppointment(...): array`
   - Validates appointment data
   - Checks: date not in past, start before end, staff availability
   - Returns: ['valid' => bool, 'errors' => array]
   - Error messages in Polish

**Business Rules Implemented:**
- Staff must have configured availability window
- Appointment must fall within availability window
- No overlapping appointments for same staff member
- 15-minute slot intervals
- Date cannot be in the past

**Notes:**
- Well-designed, testable service class
- Good separation from controllers
- **MISSING:** No buffer time between appointments
- **MISSING:** No business hours validation
- **MISSING:** No capacity management
- **FIXED INTERVAL:** 15-minute slots hardcoded (line 116)

---

## Database Schema

### Table: users
**Columns:**
- id (bigint, PK, auto-increment)
- name (varchar)
- email (varchar, unique)
- email_verified_at (timestamp, nullable)
- password (varchar)
- remember_token (varchar, nullable)
- created_at, updated_at (timestamps)

**Relationships:**
- Has many: appointments (as customer_id)
- Has many: appointments (as staff_id)
- Has many: service_availabilities (as user_id)
- Has many: model_has_roles (Spatie permission)

---

### Table: services
**Columns:**
- id (bigint, PK, auto-increment)
- name (varchar)
- description (text, nullable)
- duration_minutes (integer, default: 60)
- price (decimal(10,2), default: 0.00)
- is_active (boolean, default: true)
- sort_order (integer, default: 0)
- created_at, updated_at (timestamps)

**Indexes:**
- Primary key: id
- None explicitly defined (potential performance issue for large datasets)

---

### Table: appointments
**Columns:**
- id (bigint, PK, auto-increment)
- service_id (foreignId, references services.id, cascade on delete)
- customer_id (foreignId, references users.id, cascade on delete)
- staff_id (foreignId, references users.id, cascade on delete)
- appointment_date (date)
- start_time (time)
- end_time (time)
- status (enum: pending, confirmed, cancelled, completed, default: pending)
- notes (text, nullable)
- cancellation_reason (text, nullable)
- created_at, updated_at (timestamps)

**Indexes:**
- Primary key: id
- Foreign keys: service_id, customer_id, staff_id
- Composite: (appointment_date, start_time)
- Composite: (staff_id, appointment_date)
- Composite: (customer_id, appointment_date)

**Notes:**
- Excellent indexing for common query patterns
- Cascade delete ensures referential integrity

---

### Table: service_availabilities
**Columns:**
- id (bigint, PK, auto-increment)
- service_id (foreignId, references services.id, cascade on delete)
- user_id (foreignId, references users.id, cascade on delete)
- day_of_week (integer) - 0=Sunday, 6=Saturday
- start_time (time)
- end_time (time)
- created_at, updated_at (timestamps)

**Indexes:**
- Primary key: id
- Foreign keys: service_id, user_id
- Unique: (user_id, service_id, day_of_week, start_time)

**Notes:**
- Unique constraint prevents duplicate availability entries
- No support for date-specific exceptions

---

### Table: permissions & roles (Spatie)
Standard Spatie Laravel Permission tables:
- permissions
- roles
- model_has_permissions
- model_has_roles
- role_has_permissions

**Roles in Use:**
- super-admin
- admin
- staff
- customer

---

## API Endpoints

### Public Web Routes
```
GET  /                         HomeController@index             (public)
```

### Authentication Routes
Laravel's default authentication routes (Auth::routes()):
```
GET  /login                    Auth\LoginController@showLoginForm
POST /login                    Auth\LoginController@login
POST /logout                   Auth\LoginController@logout
GET  /register                 Auth\RegisterController@showRegistrationForm
POST /register                 Auth\RegisterController@register
GET  /password/reset           Auth\ForgotPasswordController@showLinkRequestForm
POST /password/email           Auth\ForgotPasswordController@sendResetLinkEmail
GET  /password/reset/{token}   Auth\ResetPasswordController@showResetForm
POST /password/reset           Auth\ResetPasswordController@reset
```

### Protected Routes (require auth)
```
GET  /services/{service}/book  BookingController@create         booking.create
POST /api/available-slots      BookingController@getAvailableSlots  booking.slots
GET  /my-appointments          AppointmentController@index      appointments.index
POST /appointments             AppointmentController@store      appointments.store
POST /appointments/{id}/cancel AppointmentController@cancel     appointments.cancel
```

### API Endpoint Details

#### POST /api/available-slots
**Purpose:** Get available booking time slots

**Request:**
```json
{
  "service_id": 1,
  "staff_id": 2,
  "date": "2025-10-15"
}
```

**Response:**
```json
{
  "slots": [
    {
      "start": "09:00",
      "end": "10:30",
      "datetime_start": "2025-10-15 09:00",
      "datetime_end": "2025-10-15 10:30"
    },
    {
      "start": "09:15",
      "end": "10:45",
      "datetime_start": "2025-10-15 09:15",
      "datetime_end": "2025-10-15 10:45"
    }
  ],
  "date": "2025-10-15"
}
```

**Notes:**
- Returns slots in 15-minute intervals
- Already filters out unavailable times
- Good for real-time frontend availability checking

---

## Filament Resources (Admin Panel)

### ServiceResource
**Location:** `/var/www/projects/paradocks/app/app/Filament/Resources/ServiceResource.php`

**Purpose:** Admin management of detailing services

**Features:**
- Full CRUD operations
- Form fields: name, description, duration_minutes, price, is_active, sort_order
- Table columns: name, duration_minutes, price, is_active, sort_order, timestamps
- Actions: Edit, Delete (bulk)

**Notes:**
- Basic implementation
- **MISSING:** No relation manager for service_availabilities
- **MISSING:** No image/gallery management

---

### AppointmentResource
**Location:** `/var/www/projects/paradocks/app/app/Filament/Resources/AppointmentResource.php`

**Purpose:** Admin management of appointments

**Features:**
- Full CRUD operations
- Polish language labels
- Auto-calculate end_time based on service duration
- Status badges with color coding
- Create customer inline option
- Filters: status, service, staff
- Actions: View, Edit, Delete
- Validation: prevents booking in past, checks staff availability

**Form Intelligence:**
- Reactive fields update end_time when service/start_time changes
- Shows cancellation_reason field only when status is cancelled
- Customer/Staff dropdowns filtered by role

**Table Features:**
- Columns: service, customer, staff, date, times, status
- Default sort: appointment_date DESC
- Badge colors: warning=pending, success=confirmed, danger=cancelled, secondary=completed

**Notes:**
- Well-designed admin interface
- Good UX with reactive fields
- **MISSING:** Calendar view integration (though Guava Calendar is installed)
- **MISSING:** Bulk status updates

---

### UserResource
**Location:** `/var/www/projects/paradocks/app/app/Filament/Resources/UserResource.php`

**Status:** File exists but not reviewed in detail

**Expected Features:** User management, role assignment

---

## Installed Packages & Integrations

### Core Laravel Packages
- **Laravel Framework:** 12.x
- **Laravel Sanctum:** API authentication (not actively used)
- **Spatie Laravel Permission:** v6.21 - Role and permission management

### Admin Panel
- **Laravel Filament:** v3.3+ - Complete admin panel framework

### Calendar (Installed but Not Integrated)
- **Guava Calendar:** v1.14.2 - Calendar component for Laravel
- **Status:** Installed but no evidence of active use in codebase
- **Potential:** Could be integrated for visual appointment management

### Development Tools
- Laravel Pint (code formatting)
- PHPUnit 11.5+ (testing)

### Frontend
- Vite (asset bundling)
- Tailwind CSS 4.0 (styling)

---

## Configuration Notes

### Authentication
- Standard Laravel authentication
- Session-based (web guard)
- Email verification available but not enforced

### Queue System
- Default: sync driver (no actual queue processing)
- **Recommendation:** Configure for async email/SMS notifications

### Cache
- Default configuration
- No advanced caching strategy implemented

### Database
- SQLite for development
- MySQL for Docker/production
- Good indexing on appointments table

---

## Identified Gaps for Modern Frontend Features

Based on analysis of the current backend architecture, here are the gaps that need to be addressed to support modern frontend booking experiences:

### 1. Real-Time Availability Checking
**Status:** PARTIAL SUPPORT

**Existing:**
- `/api/available-slots` endpoint exists
- Returns time slots in 15-minute intervals
- Checks staff availability and conflicts

**Missing:**
- No WebSocket/Pusher integration for real-time updates
- No optimistic locking to prevent double-booking
- No slot reservation mechanism (hold slot while user completes booking)

**Impact:** Medium - Current API works but lacks real-time coordination

---

### 2. Service Package Tiers
**Status:** NOT SUPPORTED

**Existing:**
- Single-tier services only
- Flat pricing per service

**Missing:**
- No service tier concept (Basic/Premium/Elite)
- No package bundles or combinations
- No tiered pricing structure
- No feature comparison data for frontend display

**Impact:** HIGH - Requires database schema changes

**Required Changes:**
- Add `service_tier` field or separate `ServiceTier` model
- Add `service_packages` table for bundles
- Update pricing structure to support multiple tiers

---

### 3. Add-Ons and Dynamic Pricing
**Status:** NOT SUPPORTED

**Existing:**
- Single base price per service
- No add-on concept

**Missing:**
- No add-ons table/model (e.g., "Headlight Restoration", "Pet Hair Removal")
- No pricing calculation system for base + add-ons
- No appointment_add_ons pivot table
- No price breakdown in appointment record

**Impact:** HIGH - Requires new models and pricing logic

**Required Changes:**
- Create `ServiceAddOn` model
- Create `appointment_add_ons` pivot table
- Add `total_price` field to appointments
- Implement pricing calculation service

---

### 4. Customer Data Collection
**Status:** ✅ PARTIAL IMPLEMENTATION

**Existing:**
- ✅ User profile fields: first_name, last_name, phone_e164, address fields
- ✅ Google Maps location data on appointments
- ✅ Vehicle information: VehicleType, CarBrand, CarModel, year
- Notes field on appointment

**Missing:**
- No vehicle color or plate number tracking
- No customer preferences/notes
- No marketing consent fields

**Impact:** MEDIUM - Core features implemented, minor gaps remain

**Completed:**
- ✅ Extended users table with phone, address fields (migration: 2025_10_18)
- ✅ Created VehicleType/CarBrand/CarModel system
- ✅ Vehicle fields on appointments table
- ✅ Google Maps integration for service location

---

### 5. Notification System
**Status:** NOT IMPLEMENTED

**Existing:**
- None

**Missing:**
- No email notification system
- No SMS notification capability
- No notification preferences
- No reminder scheduling
- No confirmation emails
- No cancellation notifications

**Impact:** HIGH - Essential for professional service

**Required Changes:**
- Create notification classes (AppointmentConfirmed, AppointmentReminder, etc.)
- Configure mail driver
- Integrate SMS provider (Twilio, Vonage)
- Add `notification_preferences` to users
- Implement notification scheduling (Laravel queue + scheduler)

---

### 6. Analytics & Conversion Tracking
**Status:** NOT IMPLEMENTED

**Existing:**
- Basic appointment data
- created_at timestamps

**Missing:**
- No funnel tracking (viewed service → started booking → completed)
- No abandonment tracking
- No source tracking (how customer found service)
- No referral tracking
- No conversion event logging

**Impact:** MEDIUM - Nice to have for business intelligence

**Required Changes:**
- Create `analytics_events` table
- Implement event tracking middleware
- Add source/referral fields to appointments
- Create analytics dashboard in Filament

---

### 7. Payment & Deposit System
**Status:** NOT IMPLEMENTED

**Existing:**
- Only service price stored
- No payment tracking

**Missing:**
- No payment integration (Stripe, PayPal)
- No deposit/prepayment system
- No payment status tracking
- No refund handling
- No invoice generation

**Impact:** HIGH - Critical for reducing no-shows

**Required Changes:**
- Create `payments` table
- Integrate payment gateway (Stripe recommended)
- Add payment_status to appointments
- Implement deposit collection flow
- Create invoice generation service

---

### 8. Booking Flow Enhancement
**Status:** BASIC IMPLEMENTATION

**Existing:**
- Single-step booking form
- Authentication required

**Missing:**
- No multi-step wizard
- No guest booking (collect info, then create account)
- No booking summary/review step
- No calendar view for date selection
- No service comparison view

**Impact:** MEDIUM - UX improvement

**Required Changes:**
- Implement multi-step form (service → date/time → customer info → review → confirm)
- Add guest booking capability
- Integrate Guava Calendar for date picking
- Create booking summary view

---

### 9. Staff & Capacity Management
**Status:** BASIC IMPLEMENTATION

**Existing:**
- Staff-to-service availability mapping
- One staff member per appointment

**Missing:**
- No team-based appointments (multiple staff)
- No capacity management (parallel appointments)
- No staff skills/certifications tracking
- No automatic staff assignment
- No break time management

**Impact:** MEDIUM - Scalability concern

**Required Changes:**
- Add `appointment_staff` pivot table for multiple staff
- Add capacity field to services
- Implement smart staff assignment algorithm
- Add break time configuration to service_availabilities

---

### 10. Exception & Holiday Management
**Status:** NOT IMPLEMENTED

**Existing:**
- Recurring weekly availability only

**Missing:**
- No holiday/closed day definitions
- No one-time availability exceptions
- No vacation/time-off management
- No special hours (e.g., extended hours for events)

**Impact:** MEDIUM - Operational flexibility

**Required Changes:**
- Create `availability_exceptions` table (date, user_id, type, start_time, end_time)
- Create `business_hours_exceptions` table (holidays, closures)
- Update availability checking logic to consider exceptions

---

## Summary of Required Backend Changes

### Critical (Must Have Before Frontend Implementation)
1. ✅ Customer phone number field - COMPLETED (2025-10-18)
2. ✅ Vehicle information model - COMPLETED (2025-10-31)
3. Service add-ons system
4. Dynamic pricing calculation
5. Notification system (email minimum)

### High Priority (Should Have)
1. Service tier/package system
2. Payment integration
3. Deposit handling
4. Guest booking flow
5. Address management for mobile detailing

### Medium Priority (Nice to Have)
1. Analytics tracking
2. Capacity management
3. Exception/holiday management
4. WebSocket real-time updates
5. Guava Calendar integration

### Low Priority (Future Enhancement)
1. Referral tracking
2. Advanced analytics dashboard
3. Team-based appointments
4. Skills/certification tracking

---

## Recommendations

### Immediate Actions
1. **Extend User Model** - Add phone number (required for SMS)
2. **Create Vehicle Model** - Essential for detailing business
3. **Create AddOn System** - Enable flexible service customization
4. **Implement Notifications** - At minimum, email confirmations
5. **Add API Documentation** - OpenAPI/Swagger for frontend team

### Database Migrations Needed
```php
// 1. Add phone to users
Schema::table('users', function (Blueprint $table) {
    $table->string('phone', 20)->nullable()->after('email');
    $table->text('address')->nullable();
    $table->string('city', 100)->nullable();
    $table->string('postal_code', 20)->nullable();
});

// 2. Create vehicles table
Schema::create('vehicles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
    $table->string('make', 50);
    $table->string('model', 50);
    $table->year('year');
    $table->string('color', 30)->nullable();
    $table->string('plate_number', 20)->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
});

// 3. Update appointments table
Schema::table('appointments', function (Blueprint $table) {
    $table->foreignId('vehicle_id')->nullable()->after('customer_id')
          ->constrained()->nullOnDelete();
    $table->decimal('total_price', 10, 2)->default(0)->after('service_id');
    $table->string('payment_status', 20)->default('pending')->after('total_price');
});

// 4. Create service_add_ons table
Schema::create('service_add_ons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('service_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

// 5. Create appointment_add_ons pivot
Schema::create('appointment_add_ons', function (Blueprint $table) {
    $table->id();
    $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
    $table->foreignId('service_add_on_id')->constrained()->cascadeOnDelete();
    $table->decimal('price_at_booking', 10, 2);
    $table->timestamps();
});

// 6. Create notifications table (Laravel default)
php artisan notifications:table

// 7. Create payments table
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
    $table->string('payment_intent_id')->nullable();
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('USD');
    $table->string('status', 20); // pending, succeeded, failed, refunded
    $table->string('payment_method', 50)->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();

    $table->index('payment_intent_id');
    $table->index('status');
});
```

### New Service Classes Needed
1. `PricingCalculatorService` - Calculate total with add-ons
2. `NotificationService` - Wrapper for multi-channel notifications
3. `PaymentService` - Payment gateway integration
4. `VehicleService` - Vehicle management logic
5. `BookingWorkflowService` - Multi-step booking orchestration

### API Endpoints to Add
```
GET    /api/services/{id}/add-ons          Get available add-ons for service
GET    /api/services/{id}/tiers            Get pricing tiers (future)
POST   /api/booking/reserve-slot           Reserve a time slot temporarily
POST   /api/booking/calculate-price        Calculate total with add-ons
POST   /api/booking/guest                  Guest booking flow
GET    /api/customer/vehicles              Get customer's vehicles
POST   /api/customer/vehicles              Add new vehicle
GET    /api/calendar/available-dates       Get dates with availability
POST   /api/payments/create-intent         Create Stripe payment intent
POST   /api/payments/confirm               Confirm payment
```

### Performance Optimizations
1. Add Redis cache for availability checks
2. Index services.is_active for faster queries
3. Consider database read replicas for high traffic
4. Implement API rate limiting
5. Add eager loading in relationships to avoid N+1 queries

### Testing Recommendations
1. Create Feature tests for booking flow
2. Create Unit tests for AppointmentService
3. Create Unit tests for new PricingCalculatorService
4. Add API tests for all new endpoints
5. Implement browser tests for critical user flows (Dusk)

---

## Integration Points for Frontend

### Data Format Standards

**Date Format:** YYYY-MM-DD (ISO 8601)
**Time Format:** HH:mm (24-hour)
**DateTime Format:** YYYY-MM-DD HH:mm:ss
**Currency:** Decimal with 2 places
**Phone:** E.164 format recommended

### API Response Format
All API responses follow Laravel standard:

**Success:**
```json
{
  "data": { ... },
  "message": "Success message"
}
```

**Error:**
```json
{
  "message": "Validation error",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Authentication
- Session-based for web interface
- Sanctum tokens available for SPA/mobile (not currently configured)
- CSRF token required for all POST/PUT/DELETE requests

### Validation Rules Reference
See controller validation rules above for exact requirements.

### Recommended Frontend State Management
- Maintain booking form state across steps
- Cache available slots for selected date/staff
- Debounce API calls for availability checks
- Implement optimistic UI updates with rollback

---

## Security Considerations

### Current Implementation
- CSRF protection enabled
- SQL injection protection (Eloquent ORM)
- XSS protection (Blade templates)
- Authorization checks in controllers
- Role-based access control (Spatie)

### Recommendations
1. Add rate limiting to booking endpoints
2. Implement booking fraud detection (multiple rapid bookings)
3. Add input sanitization for notes fields
4. Implement audit logging for admin actions
5. Add two-factor authentication for staff/admin
6. Configure proper CORS for API endpoints
7. Implement webhook signature verification for payments

---

## Localization

**Current Language:** Polish (pl)

**Localized Elements:**
- Filament resource labels
- Appointment status labels
- Validation error messages
- Success messages

**Not Localized:**
- Database content (services, descriptions)
- Email templates (not yet implemented)

**Recommendation:** Implement Laravel's localization for multi-language support if targeting broader market.

---

## Conclusion

The Paradocks booking system has a solid foundation with clean MVC architecture and good separation of concerns. The existing implementation handles basic booking functionality well, but requires significant enhancements to support a modern, conversion-optimized frontend experience.

**Architecture Strengths:**
- Clean service layer architecture
- Good use of query scopes
- Proper authorization checks
- Well-indexed database
- Comprehensive Filament admin panel

**Architecture Weaknesses:**
- Missing critical business features (add-ons, tiers, payments)
- No notification system
- Limited customer data collection
- No exception handling for availability
- Fixed 15-minute slot intervals (not configurable)

**Frontend Integration Readiness:** 60%

The backend can support a basic modern frontend, but will need the critical and high-priority enhancements listed above to deliver a truly competitive booking experience matching 2024-2025 industry standards.
