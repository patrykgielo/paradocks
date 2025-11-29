# Troubleshooting Guide

**Last Updated:** November 2025

This guide provides solutions to common issues across all parts of the application. For feature-specific troubleshooting, see the links at the bottom.

## Quick Diagnostics

### System Health Check

```bash
# Check Docker containers
docker compose ps

# Check Laravel application
docker compose exec app php artisan about

# Check database connection
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();

# Check queue status
docker compose exec app php artisan queue:monitor
```

## Build & Assets Issues

### CSS Not Loading / Styles Missing

**Symptoms:**
- Page loads but looks unstyled
- Tailwind classes don't work
- Browser shows 404 for CSS file

**Solutions:**
1. **Verify build ran successfully:**
   ```bash
   cd app && npm run build
   ls app/public/build/assets/
   ```

2. **Check manifest exists:**
   ```bash
   cat app/public/build/.vite/manifest.json
   ```

3. **Clear Laravel cache:**
   ```bash
   docker compose exec app php artisan optimize:clear
   ```

4. **Check @vite directive in layout:**
   ```blade
   @vite(['resources/css/app.css', 'resources/js/app.js'])
   ```

**See:** [Production Build Guide](./production-build.md)

### Vite Dev Server Not Working

**Symptoms:**
- `npm run dev` fails
- Port 5173 already in use
- HMR (hot reload) not working

**Solutions:**
1. **Kill existing Vite process:**
   ```bash
   lsof -ti:5173 | xargs kill -9
   ```

2. **Clear Vite cache:**
   ```bash
   rm -rf app/node_modules/.vite
   ```

3. **Reinstall dependencies:**
   ```bash
   cd app && npm ci
   ```

## Docker Issues

### Containers Won't Start

**Symptoms:**
- `docker compose up -d` fails
- Containers show "Exited" status
- Port binding errors

**Solutions:**
1. **Check ports are available:**
   ```bash
   sudo lsof -i :8444  # Nginx HTTPS
   sudo lsof -i :3306  # MySQL
   sudo lsof -i :6379  # Redis
   ```

2. **View container logs:**
   ```bash
   docker compose logs nginx
   docker compose logs app
   docker compose logs mysql
   ```

3. **Rebuild containers:**
   ```bash
   docker compose down
   docker compose up -d --build
   ```

**See:** [Docker Guide](./docker.md#troubleshooting)

### Permission Denied Errors

**Symptoms:**
- Laravel shows "Permission denied" for storage/logs
- Cannot write to cache
- File upload fails

**Solutions:**
```bash
# Fix storage permissions
docker compose exec app chmod -R 775 storage bootstrap/cache

# Reset file ownership
sudo chown -R $USER:$USER app/
```

## Database Issues

### Connection Refused

**Symptoms:**
- Laravel shows "Connection refused" error
- Cannot connect to MySQL
- Migration fails

**Solutions:**
1. **Check MySQL container:**
   ```bash
   docker compose ps mysql
   docker compose logs mysql
   ```

2. **Verify .env credentials:**
   ```bash
   DB_HOST=paradocks-mysql  # NOT localhost!
   DB_DATABASE=paradocks
   DB_USERNAME=paradocks
   DB_PASSWORD=password
   ```

3. **Restart MySQL:**
   ```bash
   docker compose restart mysql
   ```

**See:** [Database Schema](../architecture/database-schema.md#quick-mysql-access)

### Migration Fails

**Symptoms:**
- `php artisan migrate` shows SQL error
- Foreign key constraint fails
- Table already exists

**Solutions:**
1. **Fresh migrations (⚠️ DELETES DATA):**
   ```bash
   docker compose exec app php artisan migrate:fresh --seed
   ```

2. **Check migration status:**
   ```bash
   docker compose exec app php artisan migrate:status
   ```

3. **Rollback and retry:**
   ```bash
   docker compose exec app php artisan migrate:rollback
   docker compose exec app php artisan migrate
   ```

## Queue & Email Issues

### Emails Not Sending

**Symptoms:**
- Email notifications don't arrive
- Queue jobs stuck
- Horizon shows failed jobs

**Solutions:**
1. **Check queue worker:**
   ```bash
   docker compose ps queue horizon
   docker compose logs -f queue
   ```

2. **Check failed jobs:**
   ```bash
   docker compose exec app php artisan queue:failed
   docker compose exec app php artisan queue:retry all
   ```

3. **Verify SMTP settings:**
   - Admin Panel → System Settings → Email
   - Test connection button
   - Check `.env` for correct credentials

**See:** [Email System Troubleshooting](../features/email-system/troubleshooting.md)

### Queue Worker Not Processing Jobs

**Symptoms:**
- Jobs stay in "pending" status
- Horizon shows "inactive" supervisor
- Queue length keeps growing

**Solutions:**
1. **Restart queue worker:**
   ```bash
   docker compose restart queue horizon
   ```

2. **Check Redis connection:**
   ```bash
   docker compose exec redis redis-cli PING
   # Should return: PONG
   ```

3. **Monitor queue in real-time:**
   ```bash
   docker compose exec app php artisan queue:monitor redis:emails,redis:reminders
   ```

## Authentication & Access Issues

### Cannot Login to Admin Panel

**Symptoms:**
- Login page shows "These credentials do not match"
- User exists but can't access /admin
- Page redirects to /login

**Solutions:**
1. **Verify user has admin role:**
   ```bash
   docker compose exec app php artisan tinker
   >>> $user = User::where('email', 'admin@example.com')->first();
   >>> $user->roles()->pluck('name');
   ```

2. **Reset password:**
   ```bash
   docker compose exec app php artisan tinker
   >>> $user = User::where('email', 'admin@example.com')->first();
   >>> $user->password = Hash::make('newpassword');
   >>> $user->save();
   ```

3. **Check canAccessPanel() method:**
   ```php
   // app/Models/User.php
   public function canAccessPanel(): bool
   {
       return str_ends_with($this->email, '@example.com')
           && $this->hasVerifiedEmail();
   }
   ```

### 403 Forbidden on API Endpoints

**Symptoms:**
- Browser console shows 403 errors
- API returns "Unauthorized"
- Vehicle/service data won't load

**Solutions:**
1. **Check route middleware:**
   ```bash
   docker compose exec app php artisan route:list --path=api
   ```

2. **Verify authentication:**
   - User must be logged in
   - Check session/cookie in DevTools
   - Try logging out and back in

3. **Clear route cache:**
   ```bash
   docker compose exec app php artisan route:clear
   ```

## Feature-Specific Troubleshooting

### Google Maps Not Working

**See:** [Google Maps Troubleshooting](../features/google-maps/README.md#troubleshooting)

Common issues:
- API key not set or invalid
- Places API not enabled
- HTTP referrer restrictions blocking requests
- Map not displaying or marker not updating

### Vehicle Selection Broken

**See:** [Vehicle Management Troubleshooting](../features/vehicle-management/README.md#troubleshooting)

Common issues:
- Vehicle types not loading (seeder not run)
- Brands dropdown empty (no active brands)
- Models not showing (pivot table not populated)
- 403 errors on vehicle API endpoints

### Email Templates Not Rendering

**See:** [Email System Troubleshooting](../features/email-system/troubleshooting.md)

Common issues:
- Undefined variable in template
- SMTP authentication fails
- Gmail App Password required
- Preview button disabled (known Livewire bug)

### Settings Not Updating

**See:** [Settings System Troubleshooting](../features/settings-system/README.md#troubleshooting)

Common issues:
- ArgumentCountError in Filament page (Livewire constructor injection)
- Settings not saving (cache not cleared)
- Form validation fails (schema mismatch)

## Performance Issues

### Slow Page Load

**Solutions:**
1. **Enable caching:**
   ```bash
   docker compose exec app php artisan optimize
   docker compose exec app php artisan config:cache
   docker compose exec app php artisan route:cache
   docker compose exec app php artisan view:cache
   ```

2. **Check query performance:**
   - Enable Query Log in AppServiceProvider
   - Use Laravel Debugbar (development only)
   - Check for N+1 queries

3. **Optimize assets:**
   ```bash
   cd app && npm run build  # Minify CSS/JS
   ```

### High Memory Usage

**Solutions:**
1. **Increase PHP memory limit:**
   ```php
   // docker/php/php.ini
   memory_limit = 256M
   ```

2. **Restart containers:**
   ```bash
   docker compose restart app queue
   ```

3. **Check for memory leaks in queue jobs:**
   ```bash
   docker compose logs -f queue | grep "memory"
   ```

## Getting Help

If you're still stuck after trying these solutions:

1. **Check Laravel logs:**
   ```bash
   docker compose exec app tail -f storage/logs/laravel.log
   ```

2. **Check Docker logs:**
   ```bash
   docker compose logs -f
   ```

3. **Enable debug mode (development only):**
   ```bash
   # .env
   APP_DEBUG=true
   LOG_LEVEL=debug
   ```

4. **Search documentation:**
   - [Feature Documentation](../features/)
   - [Architecture Decisions](../decisions/)
   - [Project Map](../project_map.md)

## See Also

- [Quick Start Guide](./quick-start.md) - Initial setup
- [Commands Reference](./commands.md) - All available commands
- [Docker Guide](./docker.md) - Docker-specific issues
- [Production Build](./production-build.md) - Build troubleshooting
- [Email System Troubleshooting](../features/email-system/troubleshooting.md)
- [Google Maps Troubleshooting](../features/google-maps/README.md#troubleshooting)
- [Vehicle Management Troubleshooting](../features/vehicle-management/README.md#troubleshooting)
