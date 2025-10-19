# ADR-006: Staff Role Enforcement in Appointment System

**Status:** Accepted
**Date:** 2025-10-19
**Deciders:** Development Team

## Context

The appointment system allowed users with 'admin' and 'super-admin' roles to be assigned as staff to appointments, violating business logic that only users with 'staff' role should handle appointments.

### Problem Discovered
- Appointment #8 had `staff_id = 1` (Admin User with 'super-admin' role)
- `AppointmentService` queried `['staff', 'admin', 'super-admin']` roles
- `ServiceAvailabilitySeeder` created records for admin users
- No validation layer prevented invalid assignments

## Decision

Implement a **defense-in-depth validation strategy** with 5 layers:

1. **Service Layer** - Only query 'staff' role in all service methods
2. **Model Observer** - Validate on every appointment create/update
3. **Controller Validation** - Custom `StaffRoleRule` validation rule
4. **Filament Resource** - Validate in Create/Edit pages
5. **Testing** - Comprehensive feature and unit tests

## Consequences

### Positive
- Prevents admin users from being assigned to appointments
- Multiple validation layers ensure data integrity
- Clear error messages in Polish for users
- Data cleanup commands for existing bad data
- Comprehensive test coverage

### Negative
- Requires data migration for existing invalid appointments
- Additional validation overhead on every save
- Breaking change if any code relied on admin assignments

## Implementation

### Files Modified
- `app/Services/AppointmentService.php` - 3 role queries fixed
- `database/seeders/ServiceAvailabilitySeeder.php` - Role query fixed
- `app/Http/Controllers/AppointmentController.php` - Added StaffRoleRule
- `app/Filament/Resources/AppointmentResource.php` - Reverted incorrect fix
- `app/Filament/Resources/AppointmentResource/Pages/CreateAppointment.php` - Added validation
- `app/Filament/Resources/AppointmentResource/Pages/EditAppointment.php` - Added validation
- `app/Providers/AppServiceProvider.php` - Registered observer

### Files Created
- `app/Observers/AppointmentObserver.php` - Model-level validation
- `app/Rules/StaffRoleRule.php` - Custom validation rule
- `app/Console/Commands/AuditInvalidStaffAssignments.php` - Audit command
- `app/Console/Commands/FixInvalidStaffAssignments.php` - Fix command
- `tests/Feature/AppointmentStaffValidationTest.php` - Feature tests
- `tests/Unit/Services/AppointmentServiceTest.php` - Unit tests

### Data Cleanup
```bash
# Audit existing appointments
php artisan appointments:audit-staff

# Preview fixes
php artisan appointments:fix-staff --dry-run

# Apply fixes
php artisan appointments:fix-staff
```

## Alternatives Considered

1. **Single validation layer** - Rejected: not robust enough
2. **Soft validation with warnings** - Rejected: business rule violation must be blocked
3. **Allow admin override** - Rejected: violates business requirements

## Notes

- All error messages in Polish per application standards
- Observer uses `saveQuietly()` bypass during data cleanup
- Tests use `RefreshDatabase` for isolation
- Future: Consider role hierarchy system if business rules evolve
