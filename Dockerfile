FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

WORKDIR /var/www

COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
