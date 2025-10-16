FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install zip pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório da aplicação
WORKDIR /var/www/html

# Copia os arquivos da aplicação
COPY . .

# Instala dependências Laravel
RUN composer install --no-dev --optimize-autoloader


# Dá permissão às pastas necessárias
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configura o Apache para usar a pasta public/
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf


# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# Expõe a porta 80
EXPOSE 80

# Comando padrão do container
CMD ["apache2-foreground"]