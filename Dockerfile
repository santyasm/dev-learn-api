#########################
# Stage 1: Composer (PHP dependencies)
#########################
FROM composer:2.7 AS vendor
WORKDIR /app

# Copia apenas arquivos necessários para instalar dependências
COPY composer.json composer.lock ./

RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY artisan ./
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY config/ config/
COPY database/ database/
COPY routes/ routes/
COPY resources/ resources/
COPY .env.example .env

#########################
# Stage 2: Node (Vite build)
#########################
FROM node:22 AS node_assets
WORKDIR /app

# Copia package.json + vite config + resources
COPY package*.json ./
COPY vite.config.js ./
COPY resources/ ./resources

# Instala e builda os assets
RUN npm install
RUN npm run build

#########################
# Stage 3: PHP + Apache (final image)
#########################
FROM php:8.2-apache

# Habilita rewrite e instala extensões necessárias
RUN a2enmod rewrite \
 && apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
 && docker-php-ext-install pdo pdo_pgsql zip bcmath

WORKDIR /var/www/html

# Copia a aplicação inteira
COPY . .

# Copia dependências do Composer da Stage 1
COPY --from=vendor /app/vendor ./vendor

# Copia build do Vite da Stage 2
COPY --from=node_assets /app/public/build ./public/build

# Define permissões
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Copiar entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]

EXPOSE 8080
