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

COPY . .

COPY --from=assets /app/public/build ./public/build

RUN if [ ! -f .env ]; then cp .env.example .env; fi \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        storage/app/public \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan storage:link || true

EXPOSE 8080

CMD php artisan migrate --force && php artisan serve --host 0.0.0.0 --port $PORT
