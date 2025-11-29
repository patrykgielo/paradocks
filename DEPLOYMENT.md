# Paradocks VPS Deployment Guide

Complete guide for deploying Paradocks application to production VPS server.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Initial Server Setup](#initial-server-setup)
3. [First Deployment](#first-deployment)
4. [Post-Deployment Configuration](#post-deployment-configuration)
5. [Regular Updates](#regular-updates)
6. [Backup & Recovery](#backup--recovery)
7. [Monitoring & Maintenance](#monitoring--maintenance)
8. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### VPS Requirements
- **Operating System**: Ubuntu 22.04 LTS or newer
- **CPU**: 2+ cores (recommended: 4 cores)
- **RAM**: 4GB minimum (recommended: 8GB)
- **Storage**: 40GB+ SSD
- **Network**: Static IP address

### Domain Configuration
- Domain name registered and DNS configured
- A record pointing to VPS IP
- (Optional) CNAME record for www subdomain

### Local Requirements
- Git repository access
- SSH key configured for VPS access

---

## Initial Server Setup

### 1. Update System
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl wget git nano ufw
```

### 2. Install Docker
```bash
# Install Docker Engine
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Add user to docker group (avoid sudo for docker commands)
sudo usermod -aG docker $USER

# Enable Docker service
sudo systemctl enable docker
sudo systemctl start docker

# Verify installation
docker --version
docker compose version  # Should show v2.x.x
```

### 3. Configure Firewall
```bash
# Allow SSH (IMPORTANT: Do this first!)
sudo ufw allow 22/tcp

# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable
sudo ufw status
```

**Security Note**: Ports 3306 (MySQL) and 6379 (Redis) should NOT be exposed to internet.

### 4. Install Certbot (Let's Encrypt)
```bash
sudo apt install -y certbot python3-certbot-nginx
```

---

## First Deployment

### 1. Clone Repository
```bash
# Clone to /var/www (recommended location)
cd /var/www
sudo git clone https://github.com/YOUR_USERNAME/paradocks.git
sudo chown -R $USER:$USER paradocks
cd paradocks
```

### 2. Create Production Environment File
```bash
# Copy example and edit with your values
cp app/.env.production.example app/.env

# Edit .env file
nano app/.env
```

**Critical settings to update**:
- `APP_URL` - Your production domain
- `DB_PASSWORD` - Strong MySQL password (32+ characters)
- `DB_ROOT_PASSWORD` - Strong MySQL root password
- `REDIS_PASSWORD` - Strong Redis password (32+ characters)
- `MAIL_USERNAME` and `MAIL_PASSWORD` - Gmail credentials with App Password
- `GOOGLE_MAPS_API_KEY` - Production API key with HTTP referrer restrictions
- `GOOGLE_MAPS_MAP_ID` - Your Map ID

**Generate strong passwords**:
```bash
# Generate 32-character password
openssl rand -base64 24
```

### 3. Run Initial Deployment Script
```bash
# Make script executable (if not already)
chmod +x scripts/deploy-init.sh

# Run initialization (requires root/sudo)
sudo ./scripts/deploy-init.sh
```

**The script will**:
1. Validate prerequisites
2. Generate Laravel APP_KEY
3. Setup Let's Encrypt SSL certificates (interactive - requires domain confirmation)
4. Build and start Docker containers
5. Run database migrations
6. Seed initial data (vehicle types, roles, email templates)
7. Prompt to create admin user
8. Optimize Laravel caches

**Expected duration**: 10-15 minutes

---

## Post-Deployment Configuration

### 1. Verify Application is Running
```bash
# Check all containers are healthy
docker compose -f docker-compose.prod.yml ps

# View logs
docker compose -f docker-compose.prod.yml logs -f
```

Visit your domain: `https://yourdomain.com`

### 2. Access Admin Panel
URL: `https://yourdomain.com/admin`

**First login**: Use the admin credentials you created during `deploy-init.sh`

### 3. Configure System Settings (Admin Panel)
Navigate to: **Admin Panel → Settings → System Settings**

Configure:
- **Email Settings**: Test SMTP connection, verify Gmail App Password
- **Contact Information**: Your business contact details
- **Booking Configuration**: Business hours, advance booking time
- **Map Configuration**: Verify Google Maps integration
- **Marketing Content**: Homepage hero, features, CTA

### 4. Test Critical Flows
- [ ] User registration (check welcome email arrives)
- [ ] Create test appointment (check confirmation email)
- [ ] Check appointment shows in admin panel
- [ ] Test Google Maps autocomplete in booking form
- [ ] Verify queue jobs processing (check Horizon dashboard: `/horizon`)

### 5. Setup Automated Backups
```bash
# Test backup script manually
sudo ./scripts/backup-database.sh

# Verify backup created
ls -lh /var/backups/paradocks/

# Setup cron job for daily backups at 2 AM
crontab -e
```

Add to crontab:
```cron
# Daily database backup at 2 AM
0 2 * * * /var/www/paradocks/scripts/backup-database.sh >> /var/log/db-backup.log 2>&1

# SSL certificate renewal (daily check, renews if <30 days)
0 3 * * * certbot renew --quiet && docker compose -f /var/www/paradocks/docker-compose.prod.yml restart nginx
```

### 6. Configure Monitoring (Optional but Recommended)

**Sentry (Error Tracking)**:
```bash
# Add to .env
SENTRY_LARAVEL_DSN=https://your-sentry-dsn
SENTRY_TRACES_SAMPLE_RATE=0.1
```

**Slack Notifications for Errors**:
```bash
# Add to .env
LOG_SLACK_WEBHOOK_URL=https://hooks.slack.com/services/YOUR/WEBHOOK/URL
```

---

## Regular Updates

Use `deploy-update.sh` for code updates with zero downtime:

```bash
cd /var/www/paradocks

# Full update (backup + migrations + cache clear + restart)
sudo ./scripts/deploy-update.sh

# Fast update without backup (if confident)
sudo ./scripts/deploy-update.sh --skip-backup

# Update code only (no Docker rebuild)
sudo ./scripts/deploy-update.sh --skip-build

# Force update (no confirmations)
sudo ./scripts/deploy-update.sh --force
```

**What the script does**:
1. Creates database backup (unless `--skip-backup`)
2. Pulls latest code from Git
3. Rebuilds Docker images (unless `--skip-build`)
4. Installs/updates Composer and NPM dependencies
5. Runs database migrations (unless `--skip-migrations`)
6. Clears and rebuilds Laravel caches
7. Gracefully restarts services (Horizon, Scheduler, Nginx)
8. Verifies deployment success

**Rollback on failure**: Script automatically rolls back Git changes if deployment fails.

---

## Backup & Recovery

### Manual Backup
```bash
# Create immediate backup
sudo ./scripts/backup-database.sh

# With custom retention (keep 60 days)
sudo ./scripts/backup-database.sh --retention-days 60

# Upload to S3 (requires AWS CLI configured)
sudo ./scripts/backup-database.sh --upload-s3 --s3-bucket your-bucket
```

### Restore from Backup
```bash
# List available backups
ls -lh /var/backups/paradocks/

# Decompress and restore
gunzip -c /var/backups/paradocks/paradocks_database_20250110_020000.sql.gz | \
docker compose -f docker-compose.prod.yml exec -T mysql \
mysql -u paradocks -p paradocks
```

### Disaster Recovery Procedure
1. Provision new VPS with same specs
2. Install Docker and dependencies (see Initial Server Setup)
3. Clone repository
4. Restore `.env` file from secure backup
5. Run `deploy-init.sh` (skip admin user creation if restoring data)
6. Restore database from latest backup
7. Restart containers: `docker compose -f docker-compose.prod.yml restart`

---

## Monitoring & Maintenance

### View Logs
```bash
# All services
docker compose -f docker-compose.prod.yml logs -f

# Specific service
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f nginx
docker compose -f docker-compose.prod.yml logs -f horizon

# Laravel log file
docker compose -f docker-compose.prod.yml exec app tail -f storage/logs/laravel.log
```

### Check Queue Status
Visit Horizon dashboard: `https://yourdomain.com/horizon`

Or command line:
```bash
docker compose -f docker-compose.prod.yml exec app php artisan horizon:status
docker compose -f docker-compose.prod.yml exec app php artisan queue:failed
```

### Performance Monitoring
```bash
# Container resource usage
docker stats

# Disk space
df -h

# Database size
docker compose -f docker-compose.prod.yml exec mysql \
mysql -u paradocks -p -e "SELECT table_schema AS 'Database',
ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.TABLES GROUP BY table_schema;"
```

### Clear Caches (If Needed)
```bash
docker compose -f docker-compose.prod.yml exec app php artisan optimize:clear
docker compose -f docker-compose.prod.yml exec app php artisan optimize
```

---

## Troubleshooting

### Application Returns 502 Bad Gateway
**Cause**: PHP-FPM container not running or crashed

**Fix**:
```bash
# Check container status
docker compose -f docker-compose.prod.yml ps app

# Restart app container
docker compose -f docker-compose.prod.yml restart app

# Check logs for errors
docker compose -f docker-compose.prod.yml logs app
```

### SSL Certificate Errors
**Cause**: Certificate expired or not generated properly

**Fix**:
```bash
# Check certificate expiry
sudo certbot certificates

# Force renewal
sudo certbot renew --force-renewal

# Restart Nginx to load new certificate
docker compose -f docker-compose.prod.yml restart nginx
```

### Queue Jobs Not Processing
**Cause**: Horizon not running or crashed

**Fix**:
```bash
# Check Horizon status
docker compose -f docker-compose.prod.yml exec app php artisan horizon:status

# Restart Horizon
docker compose -f docker-compose.prod.yml restart horizon

# Check failed jobs
docker compose -f docker-compose.prod.yml exec app php artisan queue:failed

# Retry failed jobs
docker compose -f docker-compose.prod.yml exec app php artisan queue:retry all
```

### Emails Not Sending
**Causes**: Gmail App Password incorrect, 2FA not enabled, daily limit exceeded

**Fix**:
```bash
# Test email configuration via Tinker
docker compose -f docker-compose.prod.yml exec app php artisan tinker
>>> Mail::raw('Test email', fn($msg) => $msg->to('test@example.com')->subject('Test'));
>>> exit

# Check queue logs
docker compose -f docker-compose.prod.yml logs horizon | grep -i email

# Verify Gmail settings in admin panel
# Admin Panel → Settings → Email → Test Connection
```

### High Disk Usage
**Cause**: Old backups, log files, or Docker images

**Fix**:
```bash
# Remove old backups (keeps last 30 days by default)
sudo ./scripts/backup-database.sh  # Rotation happens automatically

# Clear Laravel logs
docker compose -f docker-compose.prod.yml exec app php artisan log:clear

# Remove unused Docker images/volumes
docker system prune -a --volumes  # WARNING: Removes all unused data!
```

---

## Security Best Practices

1. **Regularly update server packages**:
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Use SSH keys (disable password authentication)**:
   ```bash
   sudo nano /etc/ssh/sshd_config
   # Set: PasswordAuthentication no
   sudo systemctl restart sshd
   ```

3. **Enable automatic security updates**:
   ```bash
   sudo apt install unattended-upgrades
   sudo dpkg-reconfigure --priority=low unattended-upgrades
   ```

4. **Monitor failed login attempts**:
   ```bash
   sudo grep "Failed password" /var/log/auth.log | tail -20
   ```

5. **Rotate passwords quarterly** (database, Redis, API keys)

6. **Review user permissions** in Filament admin panel monthly

7. **Keep Docker images updated**:
   ```bash
   docker compose -f docker-compose.prod.yml pull
   docker compose -f docker-compose.prod.yml up -d --build
   ```

---

## Support & Resources

- **Project Documentation**: `CLAUDE.md` (architecture, features, troubleshooting)
- **Email System Docs**: `docs/features/email-system/`
- **Google Maps Integration**: `CLAUDE.md` (search for "Google Maps Integration")
- **Laravel Documentation**: https://laravel.com/docs
- **Docker Compose Reference**: https://docs.docker.com/compose/

**For deployment issues**: Check logs first, then review this guide. Most issues are configuration errors in `.env` file.

---

**Last Updated**: 2025-01-10
**Version**: 1.0.0
