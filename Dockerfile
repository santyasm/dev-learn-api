FROM php:8.2-apache

# 1. Instala dependências do sistema e extensões PHP
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libpq-dev \
    && docker-php-ext-install zip pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# 2. Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Define o diretório de trabalho
WORKDIR /var/www/html

# 4. Copia os arquivos e instala as dependências do Laravel
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# 5. Ajusta as permissões de forma robusta
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

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