# Maintenance Mode - User Guide

## Overview

The maintenance mode system provides a professional way to display maintenance pages to users while allowing authorized personnel to access the system. All unauthorized requests are redirected to the home page which displays a branded maintenance template.

## Quick Start

### Enable Maintenance Mode

```bash
# Enable pre-launch mode (complete lockdown, no bypass)
docker compose exec app php artisan maintenance:enable --type=prelaunch

# Enable deployment mode (admins can bypass)
docker compose exec app php artisan maintenance:enable --type=deployment --message="System upgrade in progress"

# Enable scheduled maintenance
docker compose exec app php artisan maintenance:enable --type=scheduled --duration="30 minutes"
```

### Check Status

```bash
docker compose exec app php artisan maintenance:status
```

### Disable Maintenance Mode

```bash
docker compose exec app php artisan maintenance:disable
```

## How It Works

### Request Flow

1. **User visits any URL** (e.g., `/strona/o-nas`, `/admin`, `/api/users`)
2. **CheckMaintenanceMode middleware executes** (first in stack, before auth)
3. **Middleware checks (in order):**
   - Is this the health endpoint (`/up`)? → **Bypass** (always 200 OK)
   - Is maintenance mode NOT active? → **Bypass** (continue to app)
   - Is this `/admin` or `/admin/*`? → **Bypass** (let Filament handle auth)
   - Is this the home page (`/`)? → **Show 503** with maintenance template
   - Has valid secret token (query or session)? → **Bypass**
   - Is authenticated user admin/super-admin? → **Bypass**
   - Otherwise → **Redirect 302** to `/`
4. **Response:**
   - **If on `/up`**: Always bypass (200 OK for Docker healthchecks)
   - **If on `/admin` or `/admin/*`**: Pass through to Filament (handles auth + authorization)
   - **If already on `/`**: Return 503 with maintenance template
   - **If user can bypass**: Continue to application
   - **Otherwise**: Redirect 302 to `/` (which shows maintenance page)

### Bypass Methods

#### 1. Role-Based Bypass (Admins)

**Administrators (super-admin, admin) can ALWAYS bypass maintenance mode, including PRELAUNCH.**

**Implementation (as of PR #40):**
- `/admin` routes are **exempted** from maintenance middleware blocking
- Middleware allows ALL `/admin/*` requests to pass through to Filament
- Filament's `User::canAccessPanel()` handles authorization
- During maintenance: only super-admin and admin can access panel
- Non-admins (staff, customer) are blocked by Filament, not middleware

**Example Flow:**
```php
// 1. Admin visits /admin during maintenance
// 2. CheckMaintenanceMode middleware exempts /admin routes → allows request
// 3. Filament authenticates user via session
// 4. Filament calls User::canAccessPanel()
// 5. canAccessPanel() checks: maintenance active + user has admin role → true
// 6. Admin accesses panel ✅

// Non-admin flow:
// 1. Staff/customer visits /admin during maintenance
// 2. Middleware exempts /admin → allows request
// 3. Filament authenticates user
// 4. Filament calls User::canAccessPanel()
// 5. canAccessPanel() checks: maintenance active + user NOT admin → false
// 6. Filament blocks access ❌
```

**Why admins always have access:**
- Monitor deployment progress
- Respond to critical issues
- Manage system configuration
- Disable maintenance mode if needed

**Non-admin users** (customer, staff, guest) are blocked during ALL maintenance types and redirected to home page showing maintenance template.

#### 2. Secret Token Bypass

Generate a secret token for temporary access:

```bash
docker compose exec app php artisan maintenance:enable --type=deployment
# Token generated: paradocks-abc123xyz...
```

**Usage:**
1. Visit `https://paradocks.com?maintenance_token=paradocks-abc123xyz`
2. Token stored in session
3. Subsequent requests bypass maintenance mode
4. Token expires when maintenance mode is disabled

#### 3. Health Endpoint

`/up` always returns 200 OK (Docker healthcheck requirement).

### Maintenance Types

| Type | Duration | Bypass Allowed | Use Case |
|------|----------|----------------|----------|
| **PRELAUNCH** | 1 hour | ✅ Admins only | Before official launch |
| **DEPLOYMENT** | 5 minutes | ✅ Admins + token | Code deployments |
| **SCHEDULED** | 5 minutes | ✅ Admins + token | Planned maintenance |
| **EMERGENCY** | 2 minutes | ✅ Admins + token | Critical fixes |

## Maintenance Templates

### Pre-Launch Template

**File:** `resources/views/errors/maintenance-prelaunch.blade.php`

**Features:**
- Paradocks branding (logo, colors)
- Launch date: 03.01.2026
- Atmospheric teal background
- Contact information
- Responsive design

**Preview:** Visit `/` when maintenance mode is active.

### Deployment Template

**File:** `resources/views/errors/maintenance-deployment.blade.php`

**Features:**
- Generic maintenance message
- Estimated return time
- Contact information
- Retry-After header

## Testing

### Local Testing

1. **Enable maintenance mode:**
   ```bash
   docker compose exec app php artisan maintenance:enable --type=prelaunch
   ```

2. **Test redirects:**
   ```bash
   # Should redirect to /
   curl -I https://paradocks.local:8444/strona/o-nas
   curl -I https://paradocks.local:8444/admin

   # Should show 503
   curl -I https://paradocks.local:8444/

   # Should bypass (200 OK)
   curl -I https://paradocks.local:8444/up
   ```

3. **Disable maintenance mode:**
   ```bash
   docker compose exec app php artisan maintenance:disable
   ```

### Production Testing

**Before deploying to production:**

1. Test on staging environment
2. Verify admin bypass works
3. Verify secret token bypass works
4. Test health endpoint remains accessible
5. Verify all routes redirect correctly

## Common Issues

### Issue: Old template showing

**Symptom:** Purple gradient instead of teal Paradocks branding

**Solution:**
```bash
docker compose exec app php artisan view:clear
docker compose restart app
```

### Issue: Admin panel not accessible

**Symptom:** Admins can't access `/admin`

**Solution:** Check maintenance type. PRELAUNCH blocks everyone. Use DEPLOYMENT instead:
```bash
docker compose exec app php artisan maintenance:disable
docker compose exec app php artisan maintenance:enable --type=deployment
```

### Issue: Routes still accessible

**Symptom:** Pages load normally despite maintenance mode

**Solution:** Clear all caches and restart:
```bash
docker compose exec app php artisan optimize:clear
docker compose restart app horizon scheduler
```

## Configuration

### Customizing Templates

Edit template files:
- `resources/views/errors/maintenance-prelaunch.blade.php`
- `resources/views/errors/maintenance-deployment.blade.php`

After changes:
```bash
docker compose exec app php artisan view:clear
docker compose restart app
```

### Customizing Durations

Edit `app/Enums/MaintenanceType.php`:

```php
public function retryAfter(): int
{
    return match($this) {
        self::PRELAUNCH => 3600,      // 1 hour
        self::DEPLOYMENT => 300,      // 5 minutes (change here)
        self::SCHEDULED => 300,       // 5 minutes
        self::EMERGENCY => 120,       // 2 minutes
    };
}
```

## Architecture

### Middleware Stack

```
Request → CheckMaintenanceMode → Auth → Filament → Application
          ↑
          First middleware (prepend)
```

**Why first?**
- Blocks requests before authentication
- Prevents Filament redirects
- Ensures all routes are protected

### State Storage

Maintenance state stored in **Redis**:
- `maintenance:enabled` - Boolean flag
- `maintenance:type` - Enum value
- `maintenance:config` - JSON configuration
- `maintenance:secret_token` - Bypass token

**Persistence:** Redis data persists across container restarts.

### File Trigger (Nginx)

**File:** `storage/framework/maintenance.mode`

**Purpose:** Nginx checks file existence for pre-launch mode to serve static HTML without PHP processing.

**Content:** `"prelaunch"` (lowercase string)

## Best Practices

1. **Always test on staging first**
2. **Use DEPLOYMENT mode for deployments** (not PRELAUNCH)
3. **Communicate maintenance windows to users**
4. **Keep maintenance duration short** (< 5 minutes)
5. **Monitor health endpoint** during maintenance
6. **Clear caches after enabling/disabling**
7. **Never force-push maintenance mode changes** to production

## Related Documentation

- [Maintenance Mode Architecture](./README.md)
- [Maintenance Service API](./service-api.md)
- [Deployment Procedures](../../deployment/runbooks/ci-cd-deployment.md)
