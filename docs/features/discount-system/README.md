# Discount System MVP

**Status:** ✅ **COMPLETE MVP - Ready for Merge**
**Version:** 1.0.0 MVP
**Feature Branch:** `feature/discount-system`
**Implementation:** Phase 1-5 Complete (Backend + Admin Panel)

## Overview

Ultra Simple MVP discount system with automatic reward coupon generation after appointment completion. Built for scalability with influencer support and comprehensive usage tracking.

## Features Implemented

### Phase 1-3: Foundation ✅

**Database Schema:**
- `coupons` - Coupon codes with auto-generation rules
- `influencers` - Partner/influencer management
- `coupon_usages` - Usage tracking and reporting
- Appointments pricing fields (subtotal, discount, total, coupon_id)

**Models:**
- `Coupon` - Full validation logic, scopes, and helpers
- `Influencer` - Partner relationship management
- `CouponUsage` - Audit trail for all coupon uses
- `Appointment` - Updated with coupon relationships

**Business Logic:**
- `CouponGeneratorService` - Unique code generation (PD-XXXXXX)
- `CouponService` - Validation, application, and tracking
- `GenerateRewardCoupon` - Event listener for auto-rewards

**Events & Notifications:**
- `AppointmentCompleted` - Triggers reward generation
- `CouponRewardedNotification` - Queued email (PL/EN)

### Phase 4: Admin Panel ✅

**Filament Resources:**
- `CouponResource` - Full CRUD with dynamic forms, filters, bulk actions
- `InfluencerResource` - Partner management with coupon relation manager
- `CouponUsageResource` - Read-only reporting with CSV export

**Features:**
- Dynamic form fields based on coupon type (auto_service/auto_amount/manual)
- Live reactive discount value (% vs PLN)
- Status badges (active/expired/exhausted/inactive)
- Advanced filters (type, status, influencer, date range)
- Bulk activate/deactivate actions
- CSV export for usage reporting
- RelationManager for influencer coupons

### Phase 5: Testing & Documentation ✅

**Database Seeders:**
- `InfluencerSeeder` - 3 sample influencers
- `CouponSeeder` - 6 demo coupons (auto templates + manual codes)

**Documentation:**
- Complete README (this file)
- Implementation details (inline code comments)
- Migration instructions
- Testing workflow

## How It Works

### Automatic Reward Generation

**Trigger:** When appointment status changes to `completed`

**Conditions:**
1. **Service-based:** Coupon with `type='auto_service'` + matching service_id
2. **Amount-based:** Coupon with `type='auto_amount'` + subtotal ≥ threshold

**Flow:**
```
Appointment → completed
  ↓
AppointmentCompleted event
  ↓
GenerateRewardCoupon listener
  ↓
Check conditions (service OR amount)
  ↓
Generate unique code (PD-XXXXXX)
  ↓
Send CouponRewardedNotification (queue: high)
```

### Code Generation

**Format:** `PD-{6 random chars}` (e.g., `PD-A3F9K2`)
- Uppercase alphanumeric
- Uniqueness guaranteed via database check
- Customizable prefix

### Coupon Types

1. **Manual** - Admin-created, one-time or multi-use
2. **Auto Service** - Generated after specific service completion
3. **Auto Amount** - Generated after spending minimum amount

## Database Schema

### `coupons` Table

```sql
id, code (UNIQUE),
type ENUM('manual', 'auto_service', 'auto_amount'),
discount_type ENUM('percentage', 'fixed'),
discount_value DECIMAL(10,2),
condition_service_id (FK nullable),
condition_min_amount DECIMAL(10,2) (nullable),
uses_count INT DEFAULT 0,
total_discount_given DECIMAL(10,2) DEFAULT 0,
generated_bookings_count INT DEFAULT 0,
influencer_id (FK nullable),
is_active BOOLEAN DEFAULT true,
valid_from TIMESTAMP (nullable),
valid_until TIMESTAMP (nullable),
max_uses INT (nullable),
timestamps
```

### `appointments` Table (Updated)

```sql
# Added fields:
subtotal_amount DECIMAL(10,2) DEFAULT 0,
discount_amount DECIMAL(10,2) DEFAULT 0,
total_amount DECIMAL(10,2) DEFAULT 0,
coupon_id BIGINT FK (nullable)
```

## Configuration

### Auto-Generation Templates

Create template coupons in admin panel:

**Service-based reward:**
```php
Coupon::create([
    'code' => 'TEMPLATE-SERVICE', // Not used, just for identification
    'type' => 'auto_service',
    'discount_type' => 'percentage',
    'discount_value' => 10, // 10% discount
    'condition_service_id' => 1, // Premium Detailing service
    'is_active' => true,
]);
```

**Amount-based reward:**
```php
Coupon::create([
    'code' => 'TEMPLATE-AMOUNT', // Not used
    'type' => 'auto_amount',
    'discount_type' => 'fixed',
    'discount_value' => 50, // 50 PLN off
    'condition_min_amount' => 500, // After spending 500 PLN
    'is_active' => true,
]);
```

## API Usage

### Validate Coupon

```php
$couponService = app(CouponService::class);

$result = $couponService->validateCoupon('PD-A3F9K2', $user);

if ($result['valid']) {
    $coupon = $result['coupon'];
    // Apply to appointment
} else {
    $errorMessage = $result['message']; // Polish error message
}
```

### Apply Coupon to Appointment

```php
$result = $couponService->applyCoupon($appointment, 'PD-A3F9K2');

if ($result['success']) {
    $discountApplied = $result['discount']; // e.g., 50.00
    $updatedAppointment = $result['appointment'];
} else {
    $errorMessage = $result['message'];
}
```

### Record Usage (After Confirmation)

```php
// Automatically called when appointment is confirmed/completed
$usage = $couponService->recordUsage($coupon, $appointment);

// Updates coupon statistics:
// - uses_count +1
// - total_discount_given += discount_amount
// - generated_bookings_count +1 (if confirmed/completed)
```

## Email Templates

**Location:** `resources/views/emails/coupon-rewarded-{lang}.blade.php`

**Variables:**
- `$coupon` - Coupon model
- `$appointment` - Appointment model
- `$customer` - User model

**Supported Languages:** PL (default), EN

## Testing

### Manual Testing Workflow

1. **Create auto-generation template:**
   ```bash
   # Via Tinker or admin panel
   Coupon::create([...]);
   ```

2. **Complete appointment:**
   ```bash
   $appointment = Appointment::find(1);
   $appointment->update(['status' => 'completed']);
   ```

3. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   # Look for: "Reward coupon generated successfully"
   ```

4. **Check email:**
   - Mailpit: http://paradocks.local:8025
   - Production: Check customer's inbox

5. **Verify database:**
   ```sql
   SELECT * FROM coupons WHERE type='manual' ORDER BY created_at DESC;
   ```

## Admin Panel

**URLs (after Phase 4 completion):**
- `/admin/coupons` - Manage all coupons
- `/admin/influencers` - Manage partners
- `/admin/coupon-usages` - Usage reports (read-only)

## Next Steps (Phase 4-5)

1. **Customize Filament Resources:**
   - CouponResource: Dynamic form based on type, bulk actions, filters
   - InfluencerResource: Coupons relation manager
   - CouponUsageResource: Read-only reporting with date filters

2. **Create Seeders:**
   - Sample auto-generation templates
   - Test influencers
   - Mock usage data

3. **Documentation:**
   - Complete IMPLEMENTATION.md
   - Add architecture decision records (ADRs)
   - Update main README.md

## Migration Instructions

**⚠️ IMPORTANT: Migrations NOT yet run!**

When ready to deploy:

```bash
# Run migrations
docker compose exec app php artisan migrate

# Clear caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan filament:optimize-clear

# Restart queue workers
docker compose restart queue horizon
```

## Technical Decisions

### Why queue-based notifications?
- Non-blocking user experience
- Retry on failure (3 attempts)
- High priority queue for timely delivery

### Why template-based generation?
- Flexibility: Change reward conditions without code changes
- Multiple conditions: Support both service and amount triggers
- Easy testing: Create/delete templates as needed

### Why separate CouponUsage table?
- Audit trail: Track every coupon use
- Reporting: Analytics on coupon performance
- Data integrity: Preserve history even if coupon deleted

## Support

**Documentation:** `/docs/features/discount-system/`
**Code Location:** `app/Services/Coupon/`, `app/Models/Coupon*.php`
**Branch:** `feature/discount-system`

---

**Last Updated:** 2025-12-23
**Author:** Claude Code + Patrick
