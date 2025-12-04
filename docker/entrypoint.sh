#!/bin/sh
set -e

# Copy built assets from image to volume if volume is empty
if [ ! -f "/var/www/public/index.php" ]; then
    echo "📦 Copying application files to volume..."
    cp -r /tmp/public/* /var/www/public/
fi

exec "$@"
