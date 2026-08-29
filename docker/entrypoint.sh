#!/bin/sh
set -e

# Render assigns the listen port via $PORT — Apache must bind to it.
PORT="${PORT:-10000}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Runs pending migrations on every boot. Safe to leave on since Laravel only
# applies migrations not yet recorded in the `migrations` table — but comment
# this out if you'd rather run migrations manually before each deploy.
php artisan migrate --force

exec apache2-foreground
