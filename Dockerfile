    FROM composer:2.7 AS vendor

    WORKDIR /app
    COPY composer.json composer.lock ./
    # Copiamos o mínimo necessário para o `composer install` não falhar
    COPY database/ database/
    RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction
    
    # --- Estágio 2: Assets do Front-end (Node.js/Vite) ---
    FROM node:20-alpine AS node_assets
    
    WORKDIR /app
    # Copia os arquivos de configuração e dependências
    COPY package.json package-lock.json ./
    COPY vite.config.js ./
    # Copia os arquivos brutos que serão compilados
    COPY resources/ resources/
    
    # Instala as dependências e compila os assets para produção
    RUN npm install
    RUN npm run build
    
    # --- Estágio 3: Imagem Final (PHP + Apache) ---
    FROM php:8.2-apache
    
    # 1. Instala dependências do sistema e extensões PHP
    RUN apt-get update && apt-get install -y \
            git \
            unzip \
            libzip-dev \
            libpq-dev \
        && docker-php-ext-install zip pdo pdo_pgsql \
        && rm -rf /var/lib/apt/lists/*
    
    # 2. Define o diretório de trabalho
    WORKDIR /var/www/html
    
    # 3. Copia o código da aplicação E os assets/vendor dos estágios anteriores
    COPY . .
    COPY --from=vendor /app/vendor/ ./vendor/
    COPY --from=node_assets /app/public/build/ ./public/build/
    
    # 4. Ajusta as permissões de forma robusta
    RUN chown -R www-data:www-data /var/www/html && \
        find /var/www/html -type f -exec chmod 644 {} \; && \
        find /var/www/html -type d -exec chmod 755 {} \; && \
        chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
    
    # 5. Configuração "Força Bruta" do Apache
    RUN a2enmod rewrite && \
        echo '<VirtualHost *:80>\n\
            DocumentRoot /var/www/html/public\n\
            <Directory /var/www/html/public>\n\
                Options Indexes FollowSymLinks\n\
                AllowOverride All\n\
                Require all granted\n\
            </Directory>\n\
            ErrorLog ${APACHE_LOG_DIR}/error.log\n\
            CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
        </VirtualHost>' > /etc/apache2/sites-available/000-default.conf
    
    EXPOSE 8080
    CMD sed -i -e "s/80/${PORT:-8080}/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf && apache2-foreground