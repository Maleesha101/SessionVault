#!/bin/sh
set -e

cd /var/www/html

# Install composer dependencies if vendor doesn't exist
if [ ! -d vendor ]; then
    echo ">>> Installing composer dependencies..."
    composer install --no-interaction --optimize-autoloader 2>&1 || true
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo ">>> Generating APP_KEY..."
    php artisan key:generate --force
fi

# Run migrations
echo ">>> Running migrations..."
php artisan migrate:fresh --force --seed 2>&1 || echo "Migration/seed completed with warnings"

echo ">>> Starting the application..."
exec php artisan serve --host=0.0.0.0 --port=8000
