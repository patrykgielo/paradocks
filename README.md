# Paradocks - Car Detailing Booking System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?logo=docker)](https://docker.com)

**A modern, feature-rich booking platform for car detailing services built with Laravel 12, Filament Admin Panel, and Docker.**

---

## 🚀 Quick Start

### Docker (Recommended)

```bash
# 1. Clone & setup
git clone https://github.com/patrykgielo/paradocks.git
cd paradocks
./docker-init.sh

# 2. Add to hosts
sudo ./add-hosts-entry.sh

# 3. Access
# Development: https://paradocks.local:8444
# Admin Panel: https://paradocks.local:8444/admin
# Default credentials: admin@example.com / password
```

### Production Deployment

See comprehensive guide: **[`docs/deployment/VPS_SETUP.md`](docs/deployment/VPS_SETUP.md)**

---

## 📚 Documentation

### User & Developer Guides
- **[Complete Documentation](docs/README.md)** - All project documentation
- **[Development Guide](CLAUDE.md)** - Setup, commands, and best practices
- **[Deployment Guide](docs/deployment/VPS_SETUP.md)** - VPS production setup

### Features
- **[Email System](docs/features/email-system/README.md)** - Transactional emails with queue
- **[Booking System](docs/features/booking-system/README.md)** - Multi-step booking wizard
- **[Vehicle Management](docs/features/vehicle-management/README.md)** - Vehicle types & models
- **[Google Maps](docs/features/google-maps/README.md)** - Address autocomplete integration

### Architecture
- **[ADRs](docs/decisions/)** - Architecture Decision Records
- **[Troubleshooting](docs/features/email-system/troubleshooting.md)** - Common issues & solutions

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | Laravel 12, PHP 8.2+ |
| **Admin Panel** | Filament 3.3 |
| **Frontend** | Tailwind CSS 4.0, Vite 7 |
| **Database** | MySQL 8.0 |
| **Queue** | Redis 7.2 + Laravel Horizon |
| **Containers** | Docker + Docker Compose |
| **Testing** | PHPUnit 11.5+ |

---

## 🚢 Deployment

### Automated Deployment Script

```bash
# Deploy latest code from main branch
./scripts/deploy.sh

# Deploy with options
./scripts/deploy.sh --skip-backup --force
```

### Backup Database

```bash
# Manual backup
./scripts/backup-database.sh

# Automated (cron - daily at 3:00 AM)
0 3 * * * /var/www/paradocks/scripts/backup-database.sh
```

---

## 📝 License

**Proprietary License** - All rights reserved.

---

**Last Updated:** November 2025
**Version:** 1.0.0
**Maintainer:** Paradocks Team
