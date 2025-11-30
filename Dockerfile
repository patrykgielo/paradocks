#################################################################################
# Multi-Stage Dockerfile for Laravel 12 Application
#
# Build stages:
# 1. php-base: PHP-FPM with extensions (Alpine-based)
# 2. composer-deps: Install Composer dependencies
# 3. frontend-build: Build frontend assets with Vite
# 4. runtime: Final production image
#
# Cache strategy:
# - Stage 1 rebuilds only when PHP extensions list changes (rarely)
# - Stage 2 rebuilds only when composer.lock changes
# - Stage 3 rebuilds only when package-lock.json or resources/ change
# - Stage 4 rebuilds when application code changes
#
# Expected build times:
# - First build: 5-7 minutes
# - Code changes: 1-2 minutes
# - Dependency changes: 3-4 minutes
# - No changes: 15-30 seconds
#################################################################################

#################################################################################
# Stage 1: PHP Base with Extensions
#################################################################################
FROM php:8.2-fpm-alpine AS php-base

# Install system dependencies (Alpine packages)
# Runtime libraries only (no build tools)
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libzip \
    icu-libs \
    libxml2 \
    oniguruma

# Install build dependencies (temporary, removed after extension build)
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    $PHPIZE_DEPS

# Install PHP extensions
# Note: pdo_sqlite removed (MySQL-only app), git/zip/unzip removed (not needed in runtime)
RUN docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Remove build dependencies (reduce image size by ~150MB)
RUN apk del .build-deps

# Configure OPcache for production performance
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini && \
    echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini

# Configure PHP upload limits for CMS file uploads
RUN echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Copy Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

#################################################################################
# Stage 2: Composer Dependencies
#################################################################################
FROM php-base AS composer-deps

WORKDIR /app

# Copy dependency manifests
COPY composer.json composer.lock ./

# Install dependencies without scripts/autoloader (faster, cache-friendly)
# --no-dev: Production dependencies only
# --no-scripts: Skip post-install scripts (run in final stage)
# --no-autoloader: Skip autoloader generation (run in final stage)
# --prefer-dist: Download archives instead of cloning repos
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction

#################################################################################
# Stage 3: Frontend Build
#################################################################################
FROM node:20-alpine AS frontend-build

WORKDIR /app

# Copy dependency manifests
COPY package.json package-lock.json ./

# Install ALL dependencies (including devDependencies needed for build)
RUN npm ci --production=false

# Copy source files needed for build
COPY resources/ resources/
COPY vite.config.js ./
COPY scripts/generate-theme.js ./scripts/
COPY design-system.json ./

# Build production assets
RUN npm run build

#################################################################################
# Stage 4: Final Runtime
#################################################################################
FROM php-base AS runtime

# Accept UID/GID as build arguments for host permission matching
ARG USER_ID=1000
ARG GROUP_ID=1000

# Create laravel user with dynamic UID/GID (Alpine syntax)
# -D: Don't assign password
# -u: User ID
# -G: Primary group
RUN addgroup -g ${GROUP_ID} laravel && \
    adduser -D -u ${USER_ID} -G laravel laravel && \
    addgroup laravel www-data

# Set working directory
WORKDIR /var/www

# Copy vendor/ from composer-deps stage
COPY --chown=laravel:laravel --from=composer-deps /app/vendor/ ./vendor/

# Copy built frontend assets from frontend-build stage
COPY --chown=laravel:laravel --from=frontend-build /app/public/build/ ./public/build/

# Copy application code
COPY --chown=laravel:laravel . .

# Generate optimized autoloader (now that all files are present)
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Cache Laravel routes and views (config:cache removed - must use production .env at runtime)
# Note: Config cache is generated in deploy script with production .env to ensure correct DB connection
RUN php artisan route:cache && \
    php artisan view:cache

# Ensure storage and cache directories are writable
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views && \
    chown -R laravel:laravel storage bootstrap/cache

# Switch to non-root user
USER laravel

# Expose PHP-FPM port
EXPOSE 9000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --retries=3 \
    CMD php -v || exit 1

# Start PHP-FPM
CMD ["php-fpm"]
