FROM composer:2.7 as vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --no-dev --optimize-autoloader

FROM node:22 as node_assets
WORKDIR /app
COPY package.json package.json
COPY package-lock.json package-lock.json
COPY resources/ resources/
RUN npm install
RUN npm run build

FROM php:8.2-apache
RUN a2enmod rewrite
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath

COPY --chown=www-data:www-data . /var/www/html
COPY --from=vendor /app/vendor/ /var/www/html/vendor/
COPY --from=node_assets /app/public/build/ /var/www/html/public/build/

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache