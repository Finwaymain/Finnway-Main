#!/bin/bash
set -e

cd /var/www/fiinway-backend || cd "$(dirname "$0")"

echo "Pulling latest code from origin main..."
git fetch origin main
git reset --hard origin/main

echo "Installing Composer dependencies (if needed)..."
export COMPOSER_ALLOW_SUPERUSER=1
composer install --no-dev --optimize-autoloader --no-interaction

echo "Running database migrations (includes Food Delivery tables)..."
php artisan migrate --force

echo "Ensuring storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "Clearing and rebuilding Laravel cache..."
php artisan optimize:clear
php artisan package:discover
php artisan optimize
php artisan queue:restart 2>/dev/null || true

echo "Fixing permissions..."
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -h www-data:www-data public/storage 2>/dev/null || true

echo "✅ Auto deployment complete!"
