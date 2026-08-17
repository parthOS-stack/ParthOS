# DevOS / Laravel 13 — Render.com
# Laravel 13 + composer.json require PHP ^8.3 (php:8.2-cli will fail composer install)

# ---- Frontend (Vite) ----
FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm run build

# ---- PHP application ----
FROM php:8.3-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
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
        pdo \
        pdo_mysql \
        pdo_pgsql \
        zip \
        gd \
        mbstring \
        xml \
        bcmath \
        fileinfo \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

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

CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port $PORT
