FROM php:8.2-fpm-alpine

# Runtime dependencies
RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    libzip \
    oniguruma \
    icu-libs \
    libxml2

# Build dependencies
RUN apk add --no-cache --virtual .build-deps \
    libpng-dev \
    libjpeg-turbo-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    $PHPIZE_DEPS

# Install PHP extensions (v0.3.5 + composer.lock requirements)
RUN docker-php-ext-configure gd --with-jpeg && \
    docker-php-ext-install -j$(nproc) \
    pdo_mysql \
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

# Autoload
RUN composer dump-autoload --optimize --no-dev

# Simple user
RUN addgroup -g 1000 laravel && \
    adduser -D -u 1000 -G laravel laravel && \
    chown -R laravel:laravel /var/www

USER laravel

CMD ["php-fpm"]
