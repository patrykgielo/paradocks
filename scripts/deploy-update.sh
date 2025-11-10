#!/usr/bin/env bash

################################################################################
# deploy-update.sh - Zero-downtime deployment update script
#
# This script handles updates to an existing production deployment with
# minimal downtime. Use this for regular code updates after initial setup.
#
# Usage: ./scripts/deploy-update.sh [OPTIONS]
#
# Options:
#   --skip-backup      Skip database backup before update
#   --skip-migrations  Skip database migrations
#   --skip-build       Skip Docker image rebuild
#   --force            Skip all confirmations
#
# Prerequisites:
#   - Application already deployed via deploy-init.sh
#   - Docker containers running
#   - Git repository configured
#
# What this script does:
#   1. Creates database backup
#   2. Pulls latest code from Git
#   3. Rebuilds Docker images (if needed)
#   4. Runs database migrations
#   5. Clears and rebuilds caches
#   6. Gracefully restarts services
#   7. Verifies deployment
#
# Author: Paradocks Development Team
# Version: 1.0.0
################################################################################

set -e  # Exit on error
set -u  # Exit on undefined variable
set -o pipefail  # Exit on pipe failure

# Color codes for output
readonly RED='\033[0;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly NC='\033[0m' # No Color

# Script configuration
readonly SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
readonly PROJECT_ROOT="$SCRIPT_DIR"
readonly APP_DIR="$PROJECT_ROOT"
readonly DOCKER_COMPOSE_FILE="${PROJECT_ROOT}/docker-compose.prod.yml"
readonly BACKUP_SCRIPT="${SCRIPT_DIR}/backup-database.sh"

# Command-line options
SKIP_BACKUP=false
SKIP_MIGRATIONS=false
SKIP_BUILD=false
FORCE=false

################################################################################
# Helper Functions
################################################################################

log() {
    echo -e "${GREEN}[INFO]${NC} $*"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $*"
}

error() {
    echo -e "${RED}[ERROR]${NC} $*" >&2
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $*"
}

prompt() {
    echo -e "${BLUE}[PROMPT]${NC} $*"
}

################################################################################
# Parse Command-Line Arguments
################################################################################

parse_arguments() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --skip-backup)
                SKIP_BACKUP=true
                shift
                ;;
            --skip-migrations)
                SKIP_MIGRATIONS=true
                shift
                ;;
            --skip-build)
                SKIP_BUILD=true
                shift
                ;;
            --force)
                FORCE=true
                shift
                ;;
            -h|--help)
                show_usage
                exit 0
                ;;
            *)
                error "Unknown option: $1"
                show_usage
                exit 1
                ;;
        esac
    done
}

show_usage() {
    cat << EOF
Usage: $0 [OPTIONS]

Zero-downtime deployment update script for production environment.

OPTIONS:
    --skip-backup       Skip database backup before update
    --skip-migrations   Skip database migrations
    --skip-build        Skip Docker image rebuild
    --force             Skip all confirmations
    -h, --help          Show this help message

EXAMPLES:
    $0                              # Full update with all steps
    $0 --skip-build                 # Update code without rebuilding images
    $0 --skip-backup --force        # Fast update without backup or prompts

EOF
}

################################################################################
# Validation Functions
################################################################################

check_prerequisites() {
    log "Checking prerequisites..."

    # Check if Docker Compose file exists
    if [[ ! -f "$DOCKER_COMPOSE_FILE" ]]; then
        error "Production docker-compose file not found: $DOCKER_COMPOSE_FILE"
        exit 1
    fi

    # Check if containers are running
    if ! docker compose -f "$DOCKER_COMPOSE_FILE" ps | grep -q "Up"; then
        error "No running containers found. Is the application deployed?"
        error "Use deploy-init.sh for initial deployment."
        exit 1
    fi

    # Check if Git repository
    if [[ ! -d "${PROJECT_ROOT}/.git" ]]; then
        warn "Not a Git repository. Code update will be skipped."
    fi

    success "Prerequisites met"
}

confirm_update() {
    if [[ "$FORCE" == true ]]; then
        return 0
    fi

    prompt "This will update the production application. Continue? (y/N): "
    read -r response
    if [[ ! "$response" =~ ^[Yy]$ ]]; then
        log "Update cancelled by user"
        exit 0
    fi
}

################################################################################
# Backup Functions
################################################################################

backup_database() {
    if [[ "$SKIP_BACKUP" == true ]]; then
        warn "Skipping database backup (--skip-backup flag)"
        return 0
    fi

    log "Creating database backup..."

    if [[ ! -f "$BACKUP_SCRIPT" ]]; then
        warn "Backup script not found: $BACKUP_SCRIPT"
        warn "Skipping backup. Consider creating backups manually."
        return 0
    fi

    if bash "$BACKUP_SCRIPT"; then
        success "Database backup created successfully"
    else
        error "Database backup failed"
        prompt "Continue without backup? (y/N): "
        read -r response
        if [[ ! "$response" =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
}

################################################################################
# Update Functions
################################################################################

pull_latest_code() {
    log "Pulling latest code from Git..."

    cd "$PROJECT_ROOT"

    if [[ ! -d ".git" ]]; then
        warn "Not a Git repository. Skipping code pull."
        return 0
    fi

    # Get current branch
    local current_branch
    current_branch=$(git rev-parse --abbrev-ref HEAD)
    log "Current branch: $current_branch"

    # Show current commit
    local current_commit
    current_commit=$(git rev-parse --short HEAD)
    log "Current commit: $current_commit"

    # Fetch latest changes
    git fetch origin

    # Check if there are changes
    if git diff --quiet "HEAD" "origin/$current_branch"; then
        log "No new changes to pull"
        return 0
    fi

    # Pull changes
    git pull origin "$current_branch"

    # Show new commit
    local new_commit
    new_commit=$(git rev-parse --short HEAD)
    success "Updated to commit: $new_commit"

    # Show changelog
    log "Changes since last deployment:"
    git log --oneline "$current_commit..$new_commit"
}

rebuild_docker_images() {
    if [[ "$SKIP_BUILD" == true ]]; then
        warn "Skipping Docker image rebuild (--skip-build flag)"
        return 0
    fi

    log "Rebuilding Docker images..."

    cd "$PROJECT_ROOT"

    # Check if Dockerfile or docker-compose changed
    if ! git diff --quiet HEAD~1 HEAD -- Dockerfile docker-compose.prod.yml docker/; then
        log "Docker configuration changed, rebuilding..."
        docker compose -f "$DOCKER_COMPOSE_FILE" build --no-cache
    else
        log "Docker configuration unchanged, using cached build..."
        docker compose -f "$DOCKER_COMPOSE_FILE" build
    fi

    success "Docker images rebuilt"
}

install_dependencies() {
    log "Installing/updating dependencies..."

    # Composer install
    log "Installing PHP dependencies..."
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app composer install --no-dev --optimize-autoloader --no-interaction

    # NPM install (if needed)
    if [[ -f "${APP_DIR}/package.json" ]]; then
        log "Installing Node.js dependencies..."
        docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app npm ci --production=false

        # Build frontend assets
        log "Building frontend assets..."
        docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app npm run build
    fi

    success "Dependencies installed"
}

run_migrations() {
    if [[ "$SKIP_MIGRATIONS" == true ]]; then
        warn "Skipping database migrations (--skip-migrations flag)"
        return 0
    fi

    log "Running database migrations..."

    # Put application in maintenance mode
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan down --retry=60

    # Run migrations
    if docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan migrate --force; then
        success "Migrations completed successfully"
    else
        error "Migrations failed!"
        # Bring application back up
        docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan up
        exit 1
    fi

    # Bring application back up
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan up

    success "Application is back online"
}

clear_and_rebuild_caches() {
    log "Clearing and rebuilding caches..."

    # Clear all caches
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan optimize:clear

    # Rebuild caches
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan optimize
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan config:cache
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan route:cache
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan view:cache
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan filament:optimize

    success "Caches rebuilt"
}

restart_services() {
    log "Restarting services..."

    cd "$PROJECT_ROOT"

    # Restart Horizon (queue worker)
    log "Restarting Horizon..."
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan horizon:terminate
    docker compose -f "$DOCKER_COMPOSE_FILE" restart horizon

    # Restart scheduler (if needed)
    log "Restarting scheduler..."
    docker compose -f "$DOCKER_COMPOSE_FILE" restart scheduler

    # Restart Nginx (to pick up any config changes)
    log "Restarting Nginx..."
    docker compose -f "$DOCKER_COMPOSE_FILE" restart nginx

    # Wait for services to be healthy
    log "Waiting for services to be healthy..."
    sleep 10

    success "Services restarted"
}

################################################################################
# Verification Functions
################################################################################

verify_deployment() {
    log "Verifying deployment..."

    # Check container status
    log "Container status:"
    docker compose -f "$DOCKER_COMPOSE_FILE" ps

    # Check if all services are running
    if docker compose -f "$DOCKER_COMPOSE_FILE" ps | grep -q "Exit"; then
        error "Some containers have exited!"
        docker compose -f "$DOCKER_COMPOSE_FILE" ps
        exit 1
    fi

    # Check application health
    local app_url
    app_url=$(grep "^APP_URL=" "${APP_DIR}/.env" | cut -d'=' -f2)

    if [[ -n "$app_url" ]]; then
        log "Checking application health at: $app_url"
        if curl -sSf "$app_url" &> /dev/null; then
            success "Application is accessible"
        else
            warn "Could not verify application accessibility"
        fi
    fi

    # Show recent logs
    log "Recent application logs:"
    docker compose -f "$DOCKER_COMPOSE_FILE" logs --tail=20 app

    success "Deployment verification completed"
}

################################################################################
# Rollback Function
################################################################################

rollback() {
    error "Deployment failed! Rolling back..."

    # Bring application back up if in maintenance mode
    docker compose -f "$DOCKER_COMPOSE_FILE" exec -T app php artisan up 2>/dev/null || true

    # Rollback Git
    if [[ -d "${PROJECT_ROOT}/.git" ]]; then
        log "Rolling back to previous commit..."
        git reset --hard HEAD~1
    fi

    error "Rollback completed. Please check logs and try again."
    exit 1
}

################################################################################
# Main Function
################################################################################

main() {
    # Setup error handler
    trap rollback ERR

    log "==================================================================="
    log "          Paradocks VPS Deployment Update"
    log "==================================================================="
    echo ""

    # Parse arguments
    parse_arguments "$@"

    # Step 1: Check prerequisites
    check_prerequisites

    # Step 2: Confirm update
    confirm_update

    # Step 3: Backup database
    backup_database

    # Step 4: Pull latest code
    pull_latest_code

    # Step 5: Rebuild Docker images
    rebuild_docker_images

    # Step 6: Install/update dependencies
    install_dependencies

    # Step 7: Run migrations
    run_migrations

    # Step 8: Clear and rebuild caches
    clear_and_rebuild_caches

    # Step 9: Restart services
    restart_services

    # Step 10: Verify deployment
    verify_deployment

    echo ""
    log "==================================================================="
    success "          Update completed successfully!"
    log "==================================================================="
    echo ""
    log "Deployment summary:"
    log "  - Database backup: $([ "$SKIP_BACKUP" == true ] && echo "Skipped" || echo "Created")"
    log "  - Code update: $(git rev-parse --short HEAD 2>/dev/null || echo "N/A")"
    log "  - Migrations: $([ "$SKIP_MIGRATIONS" == true ] && echo "Skipped" || echo "Run")"
    log "  - Docker rebuild: $([ "$SKIP_BUILD" == true ] && echo "Skipped" || echo "Completed")"
    echo ""
    log "Application is running at: $(grep "^APP_URL=" "${APP_DIR}/.env" | cut -d'=' -f2)"
    echo ""
}

# Run main function
main "$@"
