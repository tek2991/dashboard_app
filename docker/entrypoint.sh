#!/bin/bash
set -e

# Cache configuration
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Wait for MySQL to be ready if DB_HOST is set to db
if [ "$DB_HOST" = "db" ]; then
    echo "Waiting for database to be ready..."
    while ! mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent; do
        sleep 1
    done
fi

# Run migrations
echo "Running database migrations..."
# php artisan migrate --force

echo "Starting PHP-FPM..."
exec "$@"
