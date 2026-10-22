#!/bin/bash
# Clear every cache and build them again (deploy.sh runs this; you can also run it alone on the server
# after changing .env:   bash refresh-caches.sh ). Do NOT run it on a development machine: cached
# config/routes mean code changes stop showing until `php artisan optimize:clear`.
set -e

PHP="${PHP:-php}"
cd "$(dirname "$0")"

$PHP artisan optimize:clear
$PHP artisan filament:optimize-clear

$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan event:cache
$PHP artisan filament:cache-components   # MUST run after every deploy, or new admin pages "do not exist"
$PHP artisan icons:cache

echo "Caches rebuilt."
