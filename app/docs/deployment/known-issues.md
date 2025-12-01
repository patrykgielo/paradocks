# Known Issues & Gotchas

**Last Updated:** 2025-11-30
**Version:** v0.2.11
**Audience:** DevOps, Developers

---

## Overview

This document lists ALL known issues, gotchas, and common mistakes encountered during Paradocks deployments (v0.2.1-v0.2.11). Each issue includes symptoms, root cause, solution, and prevention strategy.

**Purpose:**
- Quick troubleshooting reference
- Prevent repeating past mistakes
- Document workarounds for edge cases

---

## Critical Issues (Application Fails)

### 1. Docker --no-recreate Flag Blocks Environment Variables

**Severity:** 🔴 **CRITICAL**
**Discovered:** v0.2.7
**Deployment Time Lost:** ~90 minutes (3 failed deployments)

#### Symptoms
- Environment variables added to docker-compose.yml don't appear in containers
- `printenv DB_CONNECTION` returns empty or "NOT_SET"
- Laravel uses default configuration (SQLite instead of MySQL)
- Zero errors in logs - silent failure

#### Root Cause
The `--no-recreate` flag has a hidden side effect with scaling:

```bash
# Scenario: Add DB_CONNECTION to docker-compose.yml
docker compose up -d --scale app=2 --no-recreate

# What we expected: New container gets updated environment
# What actually happened: New container gets CACHED environment (before update)
```

**Why This Happens:**
1. `--no-recreate` tells Docker: "Don't recreate existing containers"
2. When scaling (app=1→2), Docker creates NEW container
3. NEW container uses CACHED image definition (doesn't re-read docker-compose.yml)
4. Environment variables from docker-compose.yml are NOT applied

#### Solution
**Remove `--no-recreate` flag when environment variables change:**

```bash
# ❌ WRONG
docker compose up -d --scale app=2 --no-recreate

# ✅ CORRECT
docker compose up -d --scale app=2
```

#### Prevention
- Never use `--no-recreate` when adding/changing environment variables
- Use `--no-recreate` only for code deployments without config changes
- Deployment script now verifies env vars are set before continuing

#### Impact
This single issue caused 3 consecutive deployment failures (v0.2.5-v0.2.7), with each attempt taking 25-30 minutes.

---

### 2. DB_CONNECTION Must Be Explicit in docker-compose.yml

**Severity:** 🔴 **CRITICAL**
**Discovered:** v0.2.5
**Deployment Time Lost:** ~75 minutes (3 failed deployments)

#### Symptoms
```
SQLSTATE[HY000]: Database file at path [/var/www/database/database.sqlite] does not exist
```

#### Root Cause
Docker Compose environment variable hierarchy:

```
1. docker-compose.yml environment: section  ← HIGHEST
2. .env file                                 ← LOWER
3. Defaults                                  ← LOWEST
```

If `DB_CONNECTION` is only in `.env`, it's NOT passed to container!

#### Solution
**Add to docker-compose.yml:**

```yaml
services:
  app:
    env_file: .env
    environment:
      - DB_CONNECTION=mysql  # ← MUST BE EXPLICIT
```

#### Prevention
- Always define critical env vars in docker-compose.yml environment section
- Use `.env` for values, docker-compose.yml for structure
- Deployment script now verifies DB_CONNECTION is set

---

### 3. REDIS_PASSWORD Required for ALL Services

**Severity:** 🔴 **CRITICAL**
**Discovered:** v0.2.9
**Deployment Time Lost:** ~15 minutes

#### Symptoms
```
ERR NOAUTH Authentication required
```

Horizon container shows error in logs but container stays "healthy".

#### Root Cause
Redis requires authentication but REDIS_PASSWORD was only in app service, not horizon/scheduler.

**All services that use Redis need the password:**
- app (sessions, cache)
- horizon (queue processing)
- scheduler (scheduled jobs that queue)

#### Solution
**Add to ALL services:**

```yaml
services:
  app:
    environment:
      - REDIS_PASSWORD=${REDIS_PASSWORD}

  horizon:
    environment:
      - REDIS_PASSWORD=${REDIS_PASSWORD}  # ← WAS MISSING

  scheduler:
    environment:
      - REDIS_PASSWORD=${REDIS_PASSWORD}  # ← WAS MISSING
```

#### Prevention
- Use template with all env vars for all services
- Deployment script could verify REDIS_PASSWORD in all services

---

### 4. APP_KEY Required for app, horizon, AND scheduler

**Severity:** 🔴 **CRITICAL**
**Discovered:** v0.2.10
**Deployment Time Lost:** ~20 minutes

#### Symptoms
```
RuntimeException: No application encryption key has been specified.
```

HTTP 500 errors on all pages.

#### Root Cause
APP_KEY missing from docker-compose.yml. Laravel cannot encrypt/decrypt sessions, cookies, or queued jobs.

**Why all 3 services need APP_KEY:**
- app: Encrypts sessions, cookies
- horizon: Decrypts queued jobs (may contain encrypted data)
- scheduler: Scheduled commands may encrypt/decrypt data

#### Solution
**Add to ALL services:**

```yaml
services:
  app:
    environment:
      - APP_KEY=${APP_KEY}

  horizon:
    environment:
      - APP_KEY=${APP_KEY}  # ← WAS MISSING

  scheduler:
    environment:
      - APP_KEY=${APP_KEY}  # ← WAS MISSING
```

#### Prevention
- Generate APP_KEY before first deployment: `php artisan key:generate`
- Never commit APP_KEY to git
- Use same APP_KEY for all services (encryption consistency)

---

### 5. phpredis Extension Required (Not predis Package)

**Severity:** 🟡 **HIGH**
**Discovered:** v0.2.8
**Deployment Time Lost:** ~25 minutes

#### Symptoms
```
Error: Class "Redis" not found
```

Horizon container crashes immediately on startup.

#### Root Cause
Laravel Horizon requires PHP Redis **extension** (C library), not **package** (pure PHP).

**Two different implementations:**
1. **phpredis** (C extension): `pecl install redis`
2. **predis** (PHP package): `composer require predis/predis`

Horizon requires #1, we only had #2.

#### Solution
**Add to Dockerfile:**

```dockerfile
# Install Redis extension via PECL (required for Laravel Horizon)
RUN pecl install redis && \
    docker-php-ext-enable redis
```

Then update `.env`:
```bash
REDIS_CLIENT=phpredis  # Not 'predis'
```

#### Prevention
- Always use phpredis for production (5x faster than predis)
- Verify extension installed: `docker compose exec app php -m | grep redis`

#### Performance Impact
phpredis is 5x faster than predis for queue operations. Using wrong implementation would cause significant performance degradation.

---

## Medium Severity Issues (Degraded Performance)

### 6. OPcache Requires Container Restart

**Severity:** 🟡 **MEDIUM**
**Discovered:** Multiple deployments

#### Symptoms
- Code changes don't apply after deployment
- Old code still executing
- Filament resources not updating
- `php artisan optimize:clear` doesn't help

#### Root Cause
PHP has TWO OPcache instances:
1. **CLI OPcache** (used by `php artisan`) ← Cleared by `optimize:clear`
2. **PHP-FPM OPcache** (used by web server) ← NOT cleared by `optimize:clear`

`php artisan optimize:clear` only clears CLI cache!

#### Solution
**Restart containers:**

```bash
docker compose restart app horizon queue scheduler
```

#### Prevention
- Always restart containers after code deployments
- Zero-downtime deployment script restarts automatically

#### Alternative
Configure OPcache with `opcache.validate_timestamps=1` for development, but this degrades performance.

---

### 7. IPv6 Healthcheck Timeout

**Severity:** 🟡 **MEDIUM**
**Discovered:** v0.2.10

#### Symptoms
- Nginx healthcheck times out
- Container stuck in "starting" state
- Takes 30+ seconds to report healthy

#### Root Cause
`localhost` resolves to IPv6 (::1) first in some Docker networks. If IPv6 not fully configured, connection times out before falling back to IPv4 (127.0.0.1).

#### Solution
**Use explicit IPv4 address:**

```yaml
# ❌ WRONG - May timeout on IPv6
healthcheck:
  test: ["CMD", "wget", "--quiet", "--tries=1", "--spider", "http://localhost/up"]

# ✅ CORRECT - Always uses IPv4
healthcheck:
  test: ["CMD", "wget", "--quiet", "--tries=1", "--spider", "http://127.0.0.1/up"]
```

#### Prevention
- Always use `127.0.0.1` instead of `localhost` in Docker healthchecks
- Applies to all services (nginx, mysql, redis if using HTTP healthchecks)

---

### 8. GitHub Actions Healthcheck Wrong Port/Endpoint

**Severity:** 🟡 **MEDIUM**
**Discovered:** v0.2.11

#### Symptoms
GitHub Actions deployment fails at health check step:
```
❌ Health check failed!
curl -f -s http://localhost:8081/health || echo 'FAILED'
```

#### Root Cause
Workflow was copy-pasted from another project:
- Wrong port: 8081 (should be 80, nginx default)
- Wrong endpoint: /health (should be /up, Laravel 11+)

#### Solution
**Fix `.github/workflows/deploy-production.yml`:**

```yaml
# Line 144 - Environment URL
url: http://72.60.17.138  # Not :8081

# Line 294 - Health check
HEALTH_STATUS=$(ssh ... "curl -f -s http://localhost/up || echo 'FAILED'")
# Not localhost:8081/health

# Line 341 - Deployment summary
echo "**URL:** http://72.60.17.138" >> $GITHUB_STEP_SUMMARY
```

#### Prevention
- Verify healthcheck URLs match actual application configuration
- Test healthcheck command manually before deployment

---

## Low Severity Issues (Annoying but Non-Critical)

### 9. Permission Denied on storage/framework/views

**Severity:** 🟢 **LOW**
**Discovered:** v0.2.1-v0.2.3

#### Symptoms
```
file_put_contents(storage/framework/views/xxx.php): Permission denied
```

#### Root Cause
UID mismatch between container user (1000) and VPS file ownership (1002).

#### Solution
**Fix permissions in deployment script:**

```bash
docker exec --user root $container chown -R laravel:laravel /var/www/storage
```

#### Prevention
- Build Docker image with correct UID: `USER_ID=1002 GROUP_ID=1002`
- Deployment script now detects UID automatically from file ownership
- Runs chown as part of zero-downtime deployment

---

### 10. Config Cache Contains Stale Values

**Severity:** 🟢 **LOW**

#### Symptoms
- Config changes not applying
- Database still using old connection settings
- Environment variable changes ignored

#### Root Cause
`php artisan config:cache` stores resolved VALUES, not env() references.

Example:
```php
// config/database.php
'default' => env('DB_CONNECTION', 'sqlite'),

// After config:cache with DB_CONNECTION=sqlite:
'default' => 'sqlite',  // ← Hardcoded value, not env()!
```

#### Solution
**Clear config cache:**

```bash
php artisan config:clear
```

**Or regenerate:**

```bash
php artisan config:clear
php artisan config:cache  # Regenerates with current env vars
```

#### Prevention
- Never run `config:cache` during Docker build (no production .env yet)
- Run `config:cache` after deployment with production .env
- Deployment script runs `config:clear` before migrations

---

## Edge Cases & Workarounds

### 11. DB_HOST Must Use Service Name (Not localhost)

**Severity:** 🟢 **LOW**

#### Symptoms
```
SQLSTATE[HY000] [2002] Connection refused
```

#### Root Cause
In Docker network, `localhost` means "inside this container", not "on host machine".

```bash
# ❌ WRONG - Connects to inside app container (no MySQL there)
DB_HOST=localhost
DB_HOST=127.0.0.1

# ✅ CORRECT - Connects to mysql service via Docker network
DB_HOST=mysql
```

#### Solution
Use Docker Compose service name as hostname.

#### Prevention
Always use service names for inter-service communication in Docker.

---

### 12. Maintenance Mode vs Deployment Permissions

**Severity:** 🟢 **LOW**

#### Symptoms
Deployment fails with permission denied when trying to write maintenance file.

#### Root Cause
CATCH-22 problem:
1. Maintenance mode tries to write file BEFORE deployment
2. Old container has wrong UID
3. Permission denied → Build never happens

#### Solution
Use zero-downtime deployment (no maintenance mode needed):
1. Old container keeps serving
2. Build new container in background
3. Switch traffic when new container healthy

#### Prevention
Zero-downtime deployment strategy (implemented in v0.2.8+) avoids this entirely.

---

## Quick Fix Reference

### Application Won't Start

```bash
# 1. Check environment variables
docker compose exec app printenv | grep -E "APP_KEY|DB_CONNECTION|REDIS"

# 2. Clear all caches
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:clear

# 3. Restart containers
docker compose restart app horizon scheduler

# 4. Check logs
docker compose logs --tail=50 app
```

### Database Connection Issues

```bash
# 1. Verify DB_CONNECTION is set
docker compose exec app printenv DB_CONNECTION
# Should output: mysql

# 2. Test connection
docker compose exec app php artisan tinker
# Inside tinker: DB::connection()->getPDO();

# 3. Check MySQL is running
docker compose ps mysql
# Should show: Up (healthy)
```

### Redis Connection Issues

```bash
# 1. Verify REDIS_PASSWORD is set
docker compose exec app printenv REDIS_PASSWORD

# 2. Test connection
docker compose exec app php artisan tinker
# Inside tinker: Redis::ping();
# Should return: "+PONG"

# 3. Check Redis is running
docker compose ps redis
# Should show: Up (healthy)
```

### Horizon Not Working

```bash
# 1. Check Horizon is running
docker compose exec horizon php artisan horizon:status
# Should output: "Horizon is running."

# 2. Check Horizon has all env vars
docker compose exec horizon printenv | grep -E "APP_KEY|DB_CONNECTION|REDIS"

# 3. Restart Horizon
docker compose restart horizon

# 4. Check logs
docker compose logs --tail=50 horizon
```

### OPcache Not Clearing

```bash
# 1. Clear Laravel caches (only clears CLI OPcache)
docker compose exec app php artisan optimize:clear

# 2. Restart containers (clears PHP-FPM OPcache)
docker compose restart app horizon scheduler

# 3. Verify restart
docker compose ps
# Check "STATUS" column for recent restart time
```

---

## Deployment Checklist

Before every deployment, verify:

### Pre-Deployment

- [ ] All env vars in docker-compose.yml match .env file structure
- [ ] APP_KEY generated and set in .env
- [ ] DB_CONNECTION=mysql (not sqlite)
- [ ] REDIS_PASSWORD set in .env
- [ ] All passwords are strong (32+ characters)
- [ ] .env file permissions: `chmod 600 .env`

### Post-Deployment

- [ ] All containers healthy: `docker compose ps`
- [ ] APP_KEY set in all services: `docker compose exec app printenv APP_KEY`
- [ ] DB_CONNECTION=mysql: `docker compose exec app printenv DB_CONNECTION`
- [ ] REDIS_PASSWORD set: `docker compose exec app printenv REDIS_PASSWORD`
- [ ] Application responds: `curl http://localhost/up`
- [ ] No errors in logs: `docker compose logs --tail=50 app`

---

## Prevention Strategies

### 1. Use Deployment Script Validation

The `scripts/deploy-with-healthcheck.sh` now includes:

```bash
# Verify DB_CONNECTION is set
DB_CONN=$(docker exec "$new_container" printenv DB_CONNECTION)
if [ "$DB_CONN" != "mysql" ]; then
    exit_with_error "DB_CONNECTION is '$DB_CONN', expected 'mysql'"
fi
```

### 2. Environment Variable Template

Maintain complete template in docker-compose.yml:

```yaml
services:
  app:
    environment:
      - APP_ENV=production
      - APP_KEY=${APP_KEY}
      - DB_CONNECTION=mysql
      - DB_HOST=mysql
      - DB_PORT=3306
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
      - REDIS_HOST=redis
      - REDIS_PASSWORD=${REDIS_PASSWORD}
      - QUEUE_CONNECTION=redis
      - CACHE_DRIVER=redis
      - SESSION_DRIVER=redis
```

Copy this to horizon and scheduler services (all need same vars).

### 3. Pre-Deployment Validation Script

Create `scripts/validate-config.sh`:

```bash
#!/bin/bash

# Check .env file
test -f .env || { echo "❌ .env missing"; exit 1; }

# Check critical variables
grep -q "^APP_KEY=" .env || { echo "❌ APP_KEY missing"; exit 1; }
grep -q "^DB_PASSWORD=" .env || { echo "❌ DB_PASSWORD missing"; exit 1; }
grep -q "^REDIS_PASSWORD=" .env || { echo "❌ REDIS_PASSWORD missing"; exit 1; }

# Check docker-compose.yml
grep -q "APP_KEY=" docker-compose.prod.yml || { echo "❌ APP_KEY not in docker-compose"; exit 1; }
grep -q "DB_CONNECTION=mysql" docker-compose.prod.yml || { echo "❌ DB_CONNECTION not set to mysql"; exit 1; }
grep -q "REDIS_PASSWORD=" docker-compose.prod.yml || { echo "❌ REDIS_PASSWORD not in docker-compose"; exit 1; }

echo "✅ All validations passed"
```

Run before every deployment.

---

## Historical Issues (Resolved)

### Docker Bypassing UFW Firewall (Staging Only)

**Status:** Resolved in staging
**Severity:** Security issue
**Impact:** MySQL/Redis exposed to internet

Fixed by adding to `/etc/docker/daemon.json`:
```json
{
  "iptables": false
}
```

And manual iptables rules. Not applicable to production (different network setup).

---

## References

- [Deployment History](deployment-history.md) - Complete failure timeline
- [Environment Variables](environment-variables.md) - Env var hierarchy explanation
- [Docker Infrastructure](docker-infrastructure.md) - Service architecture

---

**Document Version:** 1.0
**Last Updated:** 2025-11-30
**Maintained By:** Development Team

**Update Policy:**
- Add new issues as discovered
- Move to "Historical Issues" when permanently resolved
- Update Quick Fix Reference with new solutions
