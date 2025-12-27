# Stage 1: Build frontend assets
FROM node:20-alpine AS frontend-builder

WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci

# Copy source files needed for build
COPY resources ./resources
COPY vite.config.js ./
COPY design-system.json ./
COPY public ./public
COPY scripts ./scripts

# Build frontend assets
RUN npm run build

# Stage 2: PHP runtime
FROM php:8.2-fpm-alpine

# Build argument to control OPcache configuration
# Default: production (validate_timestamps=Off for performance)
# Local: development (validate_timestamps=On for instant file changes)
ARG OPCACHE_MODE=production

# Runtime dependencies
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libzip \
    oniguruma \
    icu-libs \
    libxml2 \
    sqlite-libs

# Build dependencies
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    sqlite-dev \
    $PHPIZE_DEPS

# Install PHP extensions (v0.3.5 + composer.lock requirements + pdo_sqlite for tests)
RUN docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_sqlite \
    mbstring \
    intl \
    pcntl \
    posix \
    gd \
    zip \
    bcmath \
    fileinfo \
    dom \
    exif \
    opcache

# Install Redis extension via PECL (not a core extension)
RUN pecl install redis && \
    docker-php-ext-enable redis

# Copy OPcache configuration based on OPCACHE_MODE
# Local development: opcache-dev.ini (validate_timestamps=On)
# Production: Skip file (use PHP defaults: validate_timestamps=Off)
COPY docker/php/ /tmp/php-config/
RUN if [ "$OPCACHE_MODE" = "dev" ]; then \
        if [ -f "/tmp/php-config/opcache-dev.ini" ]; then \
            cp /tmp/php-config/opcache-dev.ini /usr/local/etc/php/conf.d/opcache.ini; \
            echo "✓ OPcache dev config installed (validate_timestamps=On)"; \
        else \
            echo "⚠ opcache-dev.ini not found, using defaults"; \
        fi; \
    else \
        echo "✓ Using default OPcache production settings (validate_timestamps=Off)"; \
    fi && \
    rm -rf /tmp/php-config

# Cleanup
RUN apk del .build-deps

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy ALL code (bez --link!)
COPY . .

# Copy built frontend assets from frontend-builder stage
COPY --from=frontend-builder /app/public/build ./public/build

# Autoload
RUN composer dump-autoload --optimize --no-dev

# Copy public directory to /tmp for entrypoint script
RUN cp -r /var/www/public /tmp/public

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# CRITICAL DOCKER USER MODEL DECISION (ADR-013)
#
# Container runs as 'laravel:laravel' (UID 1000, GID 1000), NOT www-data!
#
# Rationale:
# - UID 1000 matches typical developer's primary user (dev/prod parity)
# - Consistent ownership in dev, staging, and production environments
# - Non-root for security (reduces attack surface, best practice)
# - Simplifies permission management (no chown needed in entrypoint)
#
# IMPORTANT: Do NOT try to chown files to www-data in entrypoint.sh!
# Attempting to chown to non-existent user causes restart loops (v0.6.1 incident).
#
# See: app/docs/decisions/ADR-013-docker-user-model.md
RUN addgroup -g 1000 laravel && \
    adduser -D -u 1000 -G laravel laravel && \
    chown -R laravel:laravel /var/www && \
    chown -R laravel:laravel /tmp/public

USER laravel

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
