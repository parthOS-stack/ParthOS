# DevOS / Laravel 13 — Render.com
# composer.lock pulls Symfony 8.1 (php >=8.4.1). php:8.3-cli cannot run composer install.

# ---- Frontend (Vite) ----
FROM node:22-bookworm-slim AS assets

WORKDIR /app

# Render injects service env vars as build-args. NODE_ENV=production would
# skip every package in this repo (Vite lives in devDependencies).
ARG NODE_ENV=development
ENV NODE_ENV=development

COPY package.json package-lock.json ./
RUN npm ci --include=dev \
    && test -x node_modules/.bin/vite

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build \
    && test -f public/build/manifest.json

# ---- PHP application ----
FROM php:8.4-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        ca-certificates \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        zip \
        gd \
        bcmath \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY . .

COPY --from=assets /app/public/build ./public/build

RUN test -f composer.json && test -f artisan && test -f .env.example \
    && cp .env.example .env \
    && php -r 'file_put_contents(".env", preg_replace("/^APP_KEY=.*/m", "APP_KEY=base64:".base64_encode(random_bytes(32)), file_get_contents(".env")));' \
    && mkdir -p storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
        database \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker-entrypoint.sh

# --no-scripts: composer.json runs `php artisan package:discover`, which crashes
# without APP_KEY when APP_ENV=production (composer exit 100).
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts \
    && composer dump-autoload --optimize --no-scripts \
    && php artisan package:discover --ansi \
    && php artisan storage:link \
    && test -f vendor/autoload.php

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 8080

ENTRYPOINT ["./docker-entrypoint.sh"]