# Stage 1: Build Composer dependencies
FROM composer:2 as composer_dependencies

WORKDIR /app

# Copy composer.json and composer.lock
COPY composer.json composer.lock ./

# Install Composer dependencies (without dev dependencies)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Stage 2: Final PHP-FPM application image
FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    icu-dev \
    postgresql-dev \
    mysql-client \
    oniguruma-dev \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    intl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && rm -rf /var/cache/apk/*

# Set working directory
WORKDIR /var/www/html

# Copy Composer dependencies from the first stage
COPY --from=composer_dependencies /app/vendor vendor

# Copy the rest of the application code
COPY . .

# Set permissions for Laravel storage and cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]

