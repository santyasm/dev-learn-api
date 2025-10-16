#########################
# Stage 1: Composer (PHP dependencies)
#########################
FROM composer:2.7 AS vendor
WORKDIR /app

# Copia apenas arquivos do Composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

#########################
# Stage 2: Node (Vite build)
#########################
FROM node:22 AS node_assets
WORKDIR /app

# Copia arquivos necessários para Vite
COPY package*.json ./
COPY vite.config.js ./
COPY tsconfig.json ./
COPY resources/ ./resources

RUN npm install
RUN npm run build

#########################
# Stage 3: PHP + Apache (final image)
#########################
FROM php:8.2-apache

# Ativa mod_rewrite
RUN a2enmod rewrite

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath

# Diretório de trabalho
WORKDIR /var/www/html

# Copia o Laravel e assets já buildados
COPY --chown=www-data:www-data . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=node_assets /app/public/build ./public/build

# Permissões
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t public"]
