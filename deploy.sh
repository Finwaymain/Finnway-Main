#!/bin/bash
set -e

cd /var/www/fiinway-backend || cd "$(dirname "$0")"

echo "Pulling latest code from origin main..."
git fetch origin main
git reset --hard origin/main

echo "Clearing and rebuilding Laravel cache..."
php artisan optimize:clear
php artisan package:discover
php artisan optimize
php artisan queue:restart 2>/dev/null || true

echo "Fixing permissions..."
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "✅ Auto deployment complete!"
