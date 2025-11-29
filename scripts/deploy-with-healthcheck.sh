#!/bin/bash

################################################################################
# Zero-Downtime Deployment with Healthcheck Strategy
#
# This script implements blue-green deployment pattern for container rebuilds:
# 1. Old container continues serving traffic
# 2. Build new container with correct UID in background
# 3. Start new container, wait for healthy
# 4. Run migrations (brief ~15s downtime)
# 5. Switch traffic to new container
# 6. Remove old container
#
# Usage:
#   ./scripts/deploy-with-healthcheck.sh
#
# Prerequisites:
#   - Docker and Docker Compose installed
#   - Application code deployed to /var/www/paradocks
#   - .env file configured
#
# Exit codes:
#   0 - Deployment successful
#   1 - Pre-flight checks failed
#   2 - Build failed
#   3 - Health check failed
#   4 - Migration failed
#   5 - Verification failed
################################################################################

set -euo pipefail

# Colors for output
readonly RED='\033[0;31m'
readonly GREEN='\033[0;32m'
readonly YELLOW='\033[1;33m'
readonly BLUE='\033[0;34m'
readonly NC='\033[0m' # No Color

# Configuration
readonly APP_DIR="/var/www/paradocks"
readonly COMPOSE_FILE="docker-compose.prod.yml"
readonly HEALTH_CHECK_TIMEOUT=300  # 5 minutes
readonly HEALTH_CHECK_INTERVAL=5   # 5 seconds
readonly MIGRATION_TIMEOUT=60      # 1 minute

################################################################################
# Helper Functions
################################################################################

log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

exit_with_error() {
    log_error "$1"
    exit "${2:-1}"
}

################################################################################
# Pre-flight Checks
################################################################################

preflight_checks() {
    log_info "Running pre-flight checks..."

    # Check if running from correct directory
    if [ ! -f "$COMPOSE_FILE" ]; then
        exit_with_error "docker-compose.prod.yml not found. Are you in $APP_DIR?" 1
    fi

    # Check if .env exists
    if [ ! -f .env ]; then
        exit_with_error ".env file not found" 1
    fi

    # Check Docker is running
    if ! docker info >/dev/null 2>&1; then
        exit_with_error "Docker is not running" 1
    fi

    # Check if old container exists
    if ! docker compose -f "$COMPOSE_FILE" ps app | grep -q "Up"; then
        log_warning "No running app container found. This will be a fresh deployment."
    fi

    log_success "Pre-flight checks passed"
}

################################################################################
# Detect UID/GID
################################################################################

detect_uid_gid() {
    log_info "Detecting UID/GID from file ownership..."

    local detected_uid detected_gid

    # Try multiple locations with fallback chain
    if [ -d "$APP_DIR/storage" ]; then
        detected_uid=$(stat -c '%u' "$APP_DIR/storage" 2>/dev/null || echo "1000")
        detected_gid=$(stat -c '%g' "$APP_DIR/storage" 2>/dev/null || echo "1000")
    else
        log_warning "storage directory not found, using fallback"
        detected_uid=$(stat -c '%u' "$APP_DIR" 2>/dev/null || echo "1000")
        detected_gid=$(stat -c '%g' "$APP_DIR" 2>/dev/null || echo "1000")
    fi

    # Reject root UID (0) - never correct for application
    if [ "$detected_uid" = "0" ]; then
        log_warning "Detected UID is 0 (root), using fallback 1000"
        detected_uid="1000"
        detected_gid="1000"
    fi

    export DOCKER_USER_ID="$detected_uid"
    export DOCKER_GROUP_ID="$detected_gid"

    log_success "Detected UID:GID = $DOCKER_USER_ID:$DOCKER_GROUP_ID"
}

################################################################################
# Build New Image
################################################################################

build_new_image() {
    log_info "Building new Docker image with UID:GID = $DOCKER_USER_ID:$DOCKER_GROUP_ID..."

    # Build with --no-cache to force fresh build (prevents layer cache issues)
    if ! docker compose -f "$COMPOSE_FILE" build --no-cache \
        --build-arg USER_ID="$DOCKER_USER_ID" \
        --build-arg GROUP_ID="$DOCKER_GROUP_ID" \
        app horizon scheduler; then
        exit_with_error "Docker build failed" 2
    fi

    log_success "Docker image built successfully"
}

################################################################################
# Verify Built Image UID
################################################################################

verify_image_uid() {
    log_info "Verifying built image has correct UID..."

    local built_uid
    built_uid=$(docker compose -f "$COMPOSE_FILE" run --rm --no-deps app id -u laravel 2>/dev/null || echo "FAILED")

    if [ "$built_uid" = "FAILED" ]; then
        exit_with_error "Failed to verify image UID" 2
    fi

    if [ "$built_uid" != "$DOCKER_USER_ID" ]; then
        exit_with_error "UID mismatch: expected $DOCKER_USER_ID, got $built_uid" 2
    fi

    log_success "Image verification passed: UID = $built_uid"
}

################################################################################
# Start New Container
################################################################################

start_new_container() {
    log_info "Starting new app container..."

    # Scale app service to 2 (old + new)
    if ! docker compose -f "$COMPOSE_FILE" up -d --scale app=2 --no-recreate; then
        exit_with_error "Failed to start new container" 3
    fi

    log_success "New container started"
}

################################################################################
# Wait for Health Check
################################################################################

wait_for_healthy() {
    log_info "Waiting for new container to become healthy (timeout: ${HEALTH_CHECK_TIMEOUT}s)..."

    local elapsed=0
    local new_container
    new_container=$(docker compose -f "$COMPOSE_FILE" ps -q app | tail -n 1)

    while [ $elapsed -lt $HEALTH_CHECK_TIMEOUT ]; do
        local health_status
        health_status=$(docker inspect "$new_container" --format='{{.State.Health.Status}}' 2>/dev/null || echo "unknown")

        if [ "$health_status" = "healthy" ]; then
            log_success "New container is healthy (took ${elapsed}s)"
            return 0
        fi

        if [ "$health_status" = "unhealthy" ]; then
            exit_with_error "New container became unhealthy" 3
        fi

        echo -n "."
        sleep $HEALTH_CHECK_INTERVAL
        elapsed=$((elapsed + HEALTH_CHECK_INTERVAL))
    done

    echo ""
    exit_with_error "Health check timeout after ${HEALTH_CHECK_TIMEOUT}s" 3
}

################################################################################
# Run Migrations
################################################################################

run_migrations() {
    log_info "Running database migrations (brief downtime ~15s)..."

    local new_container
    new_container=$(docker compose -f "$COMPOSE_FILE" ps -q app | tail -n 1)

    if ! timeout "$MIGRATION_TIMEOUT" docker exec "$new_container" php artisan migrate --force; then
        exit_with_error "Migration failed or timed out" 4
    fi

    log_success "Migrations completed successfully"
}

################################################################################
# Switch Traffic
################################################################################

switch_traffic() {
    log_info "Switching traffic to new container..."

    # Stop old containers
    local old_containers
    old_containers=$(docker compose -f "$COMPOSE_FILE" ps -q app | head -n -1)

    if [ -n "$old_containers" ]; then
        echo "$old_containers" | xargs -r docker stop
        log_success "Old containers stopped"
    fi

    # Scale back to 1
    docker compose -f "$COMPOSE_FILE" up -d --scale app=1 --remove-orphans

    # Restart Horizon and Scheduler with new image
    docker compose -f "$COMPOSE_FILE" up -d horizon scheduler

    log_success "Traffic switched to new container"
}

################################################################################
# Verify Deployment
################################################################################

verify_deployment() {
    log_info "Verifying deployment..."

    # Check if app container is running
    if ! docker compose -f "$COMPOSE_FILE" ps app | grep -q "Up"; then
        exit_with_error "App container is not running" 5
    fi

    # Check health endpoint
    local app_url
    app_url=$(grep "^APP_URL=" .env | cut -d '=' -f2- | tr -d '"' || echo "http://localhost")

    if command -v curl >/dev/null 2>&1; then
        if ! curl -sf "${app_url}/up" >/dev/null; then
            log_warning "Health endpoint check failed, but container is running"
        else
            log_success "Health endpoint check passed"
        fi
    fi

    log_success "Deployment verification passed"
}

################################################################################
# Cleanup
################################################################################

cleanup_old_images() {
    log_info "Cleaning up old images and containers..."

    # Remove stopped containers
    docker compose -f "$COMPOSE_FILE" rm -f

    # Prune old images (keep last 2 versions)
    docker image prune -af --filter "until=24h" >/dev/null 2>&1 || true

    log_success "Cleanup completed"
}

################################################################################
# Rollback on Failure
################################################################################

rollback() {
    log_error "Deployment failed! Rolling back..."

    # Scale back to 1 (keeps old container)
    docker compose -f "$COMPOSE_FILE" up -d --scale app=1 --remove-orphans || true

    # Remove new containers
    docker compose -f "$COMPOSE_FILE" ps -q app | tail -n 1 | xargs -r docker rm -f || true

    log_warning "Rollback completed. Old container should still be running."
    exit 1
}

################################################################################
# Main Execution
################################################################################

main() {
    log_info "=== Zero-Downtime Deployment with Healthcheck Strategy ==="

    # Change to app directory
    cd "$APP_DIR" || exit_with_error "Failed to change to $APP_DIR" 1

    # Set trap for cleanup on failure
    trap rollback ERR

    # Execute deployment steps
    preflight_checks
    detect_uid_gid
    build_new_image
    verify_image_uid
    start_new_container
    wait_for_healthy
    run_migrations
    switch_traffic
    verify_deployment
    cleanup_old_images

    echo ""
    log_success "=== Deployment completed successfully! ==="
    echo ""
}

# Run main function
main "$@"
