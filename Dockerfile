FROM php:8.4-fpm-alpine

# System deps + PHP extensions needed by Laravel + Nutgram + PostgreSQL.
RUN apk add --no-cache \
        nginx \
        postgresql-client \
        postgresql-dev \
        libzip-dev \
        zip \
        unzip \
        git \
        curl \
        $PHPIZE_DEPS \
    && docker-php-ext-install pdo_pgsql pdo_mysql zip pcntl bcmath opcache \
    && apk del $PHPIZE_DEPS

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install deps first (better layer caching).
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy app code.
COPY . .

# Permissions for Laravel storage/bootstrap.
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Nginx config (serve PHP-FPM on port 8000).
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Entrypoint: wait for DB, run migrations/seed, set webhook, start FPM + nginx.
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

CMD ["/usr/local/bin/entrypoint.sh"]
