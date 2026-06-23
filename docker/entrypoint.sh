#!/bin/sh
set -e

echo "[entrypoint] Waiting for PostgreSQL..."
until pg_isready -h "${DB_HOST:-db}" -p "${DB_PORT:-5432}" -U "${DB_USERNAME:-postgres}"; do
    sleep 1
done

echo "[entrypoint] Preparing Laravel..."
php artisan config:cache
php artisan route:cache

echo "[entrypoint] Running migrations..."
php artisan migrate --force

echo "[entrypoint] Seeding (if empty)..."
php artisan db:seed --force

echo "[entrypoint] Starting PHP-FPM + Nginx..."
php-fpm -D
exec nginx -g 'daemon off;'
