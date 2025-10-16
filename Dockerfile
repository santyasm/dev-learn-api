FROM composer:2.7 AS vendor
WORKDIR /app
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

# Etapa 2: Node (build do Vite)
FROM node:22 AS node_assets
WORKDIR /app
COPY package*.json ./
COPY vite.config.js ./
COPY resources/ ./resources
RUN npm install
RUN npm run build

# Etapa 3: PHP + Apache
FROM php:8.2-apache

# Instala dependências e habilita mod_rewrite
RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql zip bcmath \
    && a2enmod rewrite

WORKDIR /var/www/html

# Copia aplicação
COPY . .

# Copia vendor e build do Vite
COPY --from=vendor /app/vendor ./vendor
COPY --from=node_assets /app/public/build ./public/build

# Garante permissões adequadas
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache


RUN rm /etc/apache2/sites-enabled/000-default.conf
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    DirectoryIndex index.php index.html\n\
</VirtualHost>' > /etc/apache2/sites-available/laravel.conf \
    && a2ensite laravel

EXPOSE 8080

CMD sh -c "sed -i 's/Listen 80/Listen ${PORT:-8080}/g' /etc/apache2/ports.conf && apache2-foreground"
