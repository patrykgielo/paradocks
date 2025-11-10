#!/bin/bash

#########################################################
# Database Backup Script - Paradocks Laravel Application
#
# Description: Automated MySQL database backup with compression
# Author: Paradocks Team
# Last Updated: November 2025
#
# Usage:
#   ./scripts/backup-database.sh
#
# Cron (daily at 3:00 AM):
#   0 3 * * * /var/www/paradocks/scripts/backup-database.sh
#
#########################################################

set -e  # Exit on error
set -u  # Exit on undefined variable

# ======================
# Configuration
# ======================

# Backup directory (customize as needed)
BACKUP_DIR="/var/www/paradocks/backups/database"
BACKUP_RETENTION_DAYS=30  # Keep backups for 30 days

# Docker Compose file
COMPOSE_FILE="/var/www/paradocks/docker-compose.prod.yml"

# Timestamp for backup filename
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILENAME="paradocks_db_${TIMESTAMP}.sql"
COMPRESSED_BACKUP="${BACKUP_FILENAME}.gz"

# Log file
LOG_FILE="/var/www/paradocks/storage/logs/backup.log"

# Email notification (optional - requires mail command)
NOTIFY_EMAIL="${BACKUP_NOTIFICATION_MAIL:-}"

# ======================
# Functions
# ======================

log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

send_notification() {
    local subject="$1"
    local message="$2"

    if [ -n "$NOTIFY_EMAIL" ]; then
        echo "$message" | mail -s "$subject" "$NOTIFY_EMAIL" 2>/dev/null || log "Failed to send email notification"
    fi
}

cleanup_old_backups() {
    log "Cleaning up backups older than ${BACKUP_RETENTION_DAYS} days..."
    find "$BACKUP_DIR" -name "paradocks_db_*.sql.gz" -type f -mtime +${BACKUP_RETENTION_DAYS} -delete
    log "Old backups removed"
}

# ======================
# Pre-flight Checks
# ======================

log "========================================="
log "Starting database backup process"
log "========================================="

# Check if running as correct user
if [ "$EUID" -eq 0 ]; then
    log "WARNING: Running as root. Consider using a dedicated user."
fi

# Check if Docker is running
if ! docker info >/dev/null 2>&1; then
    log "ERROR: Docker is not running"
    send_notification "Backup Failed" "Docker is not running on $(hostname)"
    exit 1
fi

# Check if MySQL container is running
if ! docker compose -f "$COMPOSE_FILE" ps mysql | grep -q "Up"; then
    log "ERROR: MySQL container is not running"
    send_notification "Backup Failed" "MySQL container is not running on $(hostname)"
    exit 1
fi

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# ======================
# Database Backup
# ======================

log "Creating database backup: ${COMPRESSED_BACKUP}"

# Get database credentials from .env
DB_DATABASE=$(grep "^DB_DATABASE=" /var/www/paradocks/.env | cut -d '=' -f2)
DB_USERNAME=$(grep "^DB_USERNAME=" /var/www/paradocks/.env | cut -d '=' -f2)
DB_PASSWORD=$(grep "^DB_PASSWORD=" /var/www/paradocks/.env | cut -d '=' -f2)

# Validate credentials
if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    log "ERROR: Database credentials not found in .env file"
    send_notification "Backup Failed" "Database credentials missing in .env on $(hostname)"
    exit 1
fi

log "Database: ${DB_DATABASE}"
log "User: ${DB_USERNAME}"

# Perform backup using mysqldump inside Docker container
if docker compose -f "$COMPOSE_FILE" exec -T mysql mysqldump \
    -u"${DB_USERNAME}" \
    -p"${DB_PASSWORD}" \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --routines \
    --triggers \
    --events \
    "${DB_DATABASE}" | gzip > "${BACKUP_DIR}/${COMPRESSED_BACKUP}"; then

    BACKUP_SIZE=$(du -h "${BACKUP_DIR}/${COMPRESSED_BACKUP}" | cut -f1)
    log "✓ Backup created successfully: ${COMPRESSED_BACKUP} (${BACKUP_SIZE})"
else
    log "ERROR: Backup failed"
    send_notification "Backup Failed" "Database backup failed on $(hostname). Check logs at ${LOG_FILE}"
    exit 1
fi

# ======================
# Verification
# ======================

log "Verifying backup integrity..."

# Check if file is not empty
if [ ! -s "${BACKUP_DIR}/${COMPRESSED_BACKUP}" ]; then
    log "ERROR: Backup file is empty"
    send_notification "Backup Failed" "Backup file is empty on $(hostname)"
    exit 1
fi

# Test gzip integrity
if gunzip -t "${BACKUP_DIR}/${COMPRESSED_BACKUP}" 2>/dev/null; then
    log "✓ Backup integrity verified"
else
    log "ERROR: Backup file is corrupted"
    send_notification "Backup Failed" "Backup file is corrupted on $(hostname)"
    exit 1
fi

# ======================
# Cleanup Old Backups
# ======================

cleanup_old_backups

# ======================
# Summary
# ======================

TOTAL_BACKUPS=$(ls -1 "${BACKUP_DIR}"/paradocks_db_*.sql.gz 2>/dev/null | wc -l)
TOTAL_SIZE=$(du -sh "${BACKUP_DIR}" | cut -f1)

log "========================================="
log "Backup completed successfully"
log "File: ${COMPRESSED_BACKUP}"
log "Size: ${BACKUP_SIZE}"
log "Location: ${BACKUP_DIR}"
log "Total backups: ${TOTAL_BACKUPS}"
log "Total backup size: ${TOTAL_SIZE}"
log "========================================="

# Send success notification
send_notification "Backup Successful" "Database backup completed successfully on $(hostname)

Backup details:
- File: ${COMPRESSED_BACKUP}
- Size: ${BACKUP_SIZE}
- Total backups: ${TOTAL_BACKUPS}
- Storage usage: ${TOTAL_SIZE}
- Location: ${BACKUP_DIR}

Log: ${LOG_FILE}"

exit 0
