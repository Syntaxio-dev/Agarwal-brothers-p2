#!/bin/bash
# Run on the SERVER after uploading new files:   bash deploy.sh
# Uses PHP from the environment; to force a version:   PHP=php8.2 bash deploy.sh
# Composer: runs only if `composer` exists on the server (Hostinger shared hosting usually gets `vendor` uploaded
# from your computer instead). Skip it on purpose with:   SKIP_COMPOSER=1 bash deploy.sh
set -e

PHP="${PHP:-php}"
cd "$(dirname "$0")"

echo "== PHP version"
$PHP -v | head -n 1

# The speed caches below (especially Filament's component list) are only used when APP_ENV=production.
APP_ENV_VALUE=$(grep -E '^APP_ENV=' .env 2>/dev/null | cut -d= -f2 | tr -d '"' || true)
if [ "$APP_ENV_VALUE" != "production" ] && [ "$APP_ENV_VALUE" != "staging" ]; then
    echo "!! WARNING: APP_ENV is '$APP_ENV_VALUE' in .env. Use APP_ENV=production on the live site (staging for a demo)."
fi

echo "== Maintenance mode on"
$PHP artisan down --retry=30 || true

if [ -z "$SKIP_COMPOSER" ] && command -v composer >/dev/null 2>&1; then
    echo "== Composer packages (no dev tools, optimised class loader)"
    composer install --optimize-autoloader --no-dev --no-interaction
fi

echo "== Folder permissions"
chmod -R 775 storage bootstrap/cache

echo "== Database changes"
$PHP artisan migrate --force

echo "== Public links and admin assets"
$PHP artisan storage:link 2>/dev/null || true
$PHP artisan filament:assets

echo "== Clear and rebuild caches"
PHP="$PHP" bash refresh-caches.sh

echo "== Maintenance mode off"
$PHP artisan up

echo "Done. Open the site, send a test enquiry and look at storage/logs/laravel.log."
