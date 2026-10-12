#!/bin/bash
# Run on the SERVER after uploading new files:   bash deploy.sh
# Uses PHP from the environment; to force a version:   PHP=php8.2 bash deploy.sh
set -e

PHP="${PHP:-php}"
cd "$(dirname "$0")"

echo "== PHP version"
$PHP -v | head -n 1

echo "== Maintenance mode on"
$PHP artisan down --retry=30 || true

echo "== Folder permissions"
chmod -R 775 storage bootstrap/cache

echo "== Database changes"
$PHP artisan migrate --force

echo "== Public links and admin assets"
$PHP artisan storage:link 2>/dev/null || true
$PHP artisan filament:assets

echo "== Clear and rebuild caches"
$PHP artisan optimize:clear
$PHP artisan filament:cache-components
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache

echo "== Maintenance mode off"
$PHP artisan up

echo "Done. Open the site, send a test enquiry and look at storage/logs/laravel.log."
