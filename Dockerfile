# Stage 1: Install Composer Dependencies
FROM composer:latest AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --prefer-dist --ignore-platform-reqs --no-scripts
COPY . .
RUN composer dump-autoload --optimize

# Stage 2: Build Frontend Assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor /app/vendor
ENV NODE_OPTIONS="--max-old-space-size=1024"
RUN npm run build

# Stage 3: Build Backend & Runtime
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . .

# Copy built vendor directory from the vendor stage
COPY --from=vendor /app/vendor /var/www/vendor

# Copy built frontend assets from the frontend stage
COPY --from=frontend /app/public/build /var/www/public/build

# Set correct permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache

# Expose port 9000 and start php-fpm server
EXPOSE 9000

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
