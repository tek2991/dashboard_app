#!/bin/bash
set -e

# Wait for MySQL to be ready if DB_HOST is set to db
if [ "$DB_HOST" = "db" ]; then
    echo "Waiting for database to be ready..."
    while ! php -r "
        \$host = getenv('DB_HOST');
        \$port = getenv('DB_PORT') ?: 3306;
        \$user = getenv('DB_USERNAME');
        \$pass = getenv('DB_PASSWORD');
        try {
            new PDO(\"mysql:host=\$host;port=\$port\", \$user, \$pass);
            exit(0);
        } catch (\Throwable \$e) {
            echo 'DB Connection failed: ' . \$e->getMessage() . PHP_EOL;
            exit(1);
        }
    "; do
        sleep 2
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
