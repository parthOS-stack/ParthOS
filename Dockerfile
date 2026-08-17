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

RUN test -f composer.json && test -f artisan && test -f .env.example
RUN cp .env.example .env
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
RUN ls -la vendor
RUN ls -la vendor/autoload.php

RUN mkdir -p storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan storage:link

EXPOSE 8080

# Exec form so Docker does not empty $PORT at build time (shell form would).
CMD ["sh", "-c", "if [ -n \"$APP_KEY\" ]; then echo 'APP_KEY_PRESENT'; else echo 'APP_KEY_MISSING'; fi; php artisan config:clear && php artisan migrate --force && php artisan serve --host 0.0.0.0 --port $PORT"]