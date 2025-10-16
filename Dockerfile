#########################
# Stage 1: Composer (PHP dependencies)
#########################
FROM composer:2.7 AS vendor
WORKDIR /app

# Copia arquivos necessários para rodar composer
COPY composer.json composer.lock ./
COPY artisan ./
COPY app/ app/
COPY bootstrap/ bootstrap/
COPY config/ config/
COPY database/ database/
COPY routes/ routes/
COPY resources/ resources/
COPY .env.example .env

RUN composer install --no-dev --optimize-autoloader --no-interaction

#########################
# Stage 2: Node (Vite build)
#########################
FROM node:22 AS node_assets
WORKDIR /app

COPY package*.json ./
COPY vite.config.js ./
COPY resources/ ./resources

RUN npm install
RUN npm run build

#########################
# Stage 3: PHP + Apache (final image)
#########################
FROM php:8.2-apache

# Habilita mod_rewrite
RUN a2enmod rewrite

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath

# Define diretório da aplicação
WORKDIR /var/www/html

# Copia toda a aplicação
COPY --chown=www-data:www-data . .

# Copia vendor do stage 1
COPY --from=vendor /app/vendor ./vendor

# Copia build do Vite do stage 2
COPY --from=node_assets /app/public/build ./public/build

# Ajusta permissões
RUN chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

# Configura DocumentRoot para servir assets corretamente
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Expõe porta
EXPOSE 80

# Inicia Apache em foreground
CMD ["apache2-foreground"]
