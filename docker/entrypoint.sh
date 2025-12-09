#!/bin/sh
set -e

echo "🚀 Starting Laravel entrypoint..."

# Wait for database
while ! nc -z paradocks-mysql 3306; do
    echo "⏳ Waiting for database..."
    sleep 1
done
echo "✅ Database ready!"

# Create storage directories if they don't exist
mkdir -p /var/www/storage/app/public/services/images
mkdir -p /var/www/storage/app/public/services/featured
mkdir -p /var/www/storage/app/public/services/galleries
mkdir -p /var/www/storage/app/public/portfolio
mkdir -p /var/www/storage/app/public/avatars
mkdir -p /var/www/storage/framework/{cache,sessions,views}
mkdir -p /var/www/storage/logs

# Set proper permissions (only in production where volumes aren't mounted from host)
# In local development, skip chown because files are owned by host user
if [ "$APP_ENV" = "production" ]; then
    echo "🔒 Setting production permissions..."
    # Try to change ownership, but don't fail if it's not possible
    # (volumes may already exist with host user ownership)
    chown -R www-data:www-data /var/www/storage 2>/dev/null || echo "⚠️  Could not change storage ownership (may be owned by host), continuing..."
    chown -R www-data:www-data /var/www/bootstrap/cache 2>/dev/null || echo "⚠️  Could not change cache ownership, continuing..."
    # Set permissions (this should work even if ownership change failed)
    chmod -R 775 /var/www/storage 2>/dev/null || echo "⚠️  Could not set storage permissions"
    chmod -R 775 /var/www/bootstrap/cache 2>/dev/null || echo "⚠️  Could not set cache permissions"
else
    echo "🔓 Skipping permission changes in development (files owned by host user)"
fi

# Create storage symlink if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link
else
    echo "✅ Storage symlink already exists"
fi

# Production optimizations
if [ "$APP_ENV" = "production" ]; then
    echo "🗄️ Running migrations..."
    php artisan migrate --force

    echo "🧹 Optimizing application..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "✅ Application ready!"

# Start PHP-FPM
exec "$@"
