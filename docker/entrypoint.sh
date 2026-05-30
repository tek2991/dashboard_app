#!/bin/bash
set -e

# Wait for MySQL to be ready if DB_HOST is set to db
if [ "$DB_HOST" = "db" ]; then
    echo "Waiting for database to be ready..."
    while ! php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); } catch (\Exception \$e) { exit(1); }" > /dev/null 2>&1; do
        sleep 1
    done
    echo "Database is ready!"
fi

# Fix permissions for mounted volumes
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Cache configuration
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running database migrations..."
# php artisan migrate --force

echo "Starting PHP-FPM..."
exec "$@"
