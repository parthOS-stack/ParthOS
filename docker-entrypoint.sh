#!/bin/sh
set -e

# Empty APP_KEY from Render would override .env and crash Laravel. Drop it.
if [ -z "${APP_KEY}" ]; then
    unset APP_KEY
fi

if [ -z "${APP_KEY}" ]; then
    php artisan key:generate --force --no-interaction
fi

php artisan migrate --force
php artisan db:seed --force

exec php artisan serve --host 0.0.0.0 --port "${PORT:-8080}"
