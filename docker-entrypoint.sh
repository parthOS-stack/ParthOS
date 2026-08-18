#!/bin/sh
set -e

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    bootstrap/cache

chmod -R 775 storage bootstrap/cache

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    SQLITE_PATH="${DB_DATABASE:-/app/database/database.sqlite}"
    mkdir -p "$(dirname "$SQLITE_PATH")"
    touch "$SQLITE_PATH"
fi

# Empty APP_KEY from Render would override .env and crash Laravel. Drop it.
if [ -z "${APP_KEY}" ]; then
    unset APP_KEY
fi

if [ -z "${APP_KEY}" ]; then
    php artisan key:generate --force --no-interaction
fi

if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan migrate --force

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
