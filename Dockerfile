# --- Estágio 1: Assets do Front-end (Node.js/Vite) ---
FROM node:20-alpine AS node_assets
WORKDIR /app
COPY package.json package-lock.json ./
# MUDANÇA CRÍTICA: Copia a configuração do Vite e do PostCSS
COPY vite.config.js ./
COPY resources/ resources/
RUN npm install
RUN npm run build

# --- Estágio 2: Imagem Final com PHP-FPM e Caddy ---
FROM php:8.2-fpm-alpine

# Instala dependências do sistema e o Caddy
RUN apk add --no-cache caddy libzip-dev postgresql-dev
RUN docker-php-ext-install zip pdo pdo_pgsql

WORKDIR /var/www/html

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copia os arquivos de configuração, código e assets compilados
COPY Caddyfile /etc/caddy/Caddyfile
COPY . .
COPY --from=node_assets /app/public/build/ ./public/build/

# Instala dependências do Composer e publica assets de pacotes
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN php artisan vendor:publish --tag=l5-swagger-assets --force

# Copia e torna o script de inicialização executável
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html

# Expõe a porta e inicia os serviços
EXPOSE 8080
CMD ["start.sh"]