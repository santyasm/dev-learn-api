# --- Estágio 1: Assets do Front-end (Node.js/Vite) ---
FROM node:20-alpine AS node_assets
WORKDIR /app
COPY package.json package-lock.json ./
COPY vite.config.js ./
COPY resources/ resources/
RUN npm install
RUN npm run build

# --- Estágio 2: Imagem Final com PHP-FPM e Caddy ---
FROM php:8.2-fpm-alpine


RUN apk add --no-cache caddy libzip-dev postgresql-dev

RUN docker-php-ext-install zip pdo pdo_pgsql

WORKDIR /var/www/html

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


COPY Caddyfile /etc/caddy/Caddyfile
# Copia o código da aplicação e os assets/vendor
COPY . .
COPY --from=node_assets /app/public/build/ ./public/build/

# Instala dependências do Composer
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta e inicia o Caddy (que gerencia o PHP-FPM)
EXPOSE 8080
CMD ["caddy", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]