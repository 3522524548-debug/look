# Build frontend assets
FROM node:18-alpine AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY public ./public
COPY vite.config.js tailwind.config.js postcss.config.js jsconfig.json ./
RUN npm run build

# Install PHP dependencies
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Runtime image
FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev zip unzip \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . /var/www/html
COPY --from=vendor /app/vendor /var/www/html/vendor
COPY --from=node-build /app/public/build /var/www/html/public/build

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
