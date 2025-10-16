
FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev \
    && docker-php-ext-install pdo_pgsql

WORKDIR /var/www
COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction


CMD php -S 0.0.0.0:${PORT:-8000} -t public
