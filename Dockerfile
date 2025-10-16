FROM php:8.2-apache

# 1. INSTALA DEPENDÊNCIAS DO SISTEMA E EXTENSÕES PHP
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libpq-dev \
    && docker-php-ext-install zip pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# 2. INSTALA O COMPOSER
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. CONFIGURA O DIRETÓRIO DE TRABALHO
WORKDIR /var/www/html

# 4. COPIA OS ARQUIVOS E INSTALA DEPENDÊNCIAS DO LARAVEL
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# 5. AJUSTA AS PERMISSÕES DE FORMA ROBUSTA
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 6. CONFIGURA O APACHE CORRETAMENTE
# Habilita o mod_rewrite e sobrescreve o arquivo de configuração do site
# para apontar para a pasta /public e permitir o uso do .htaccess
RUN a2enmod rewrite
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# 7. EXPÕE A PORTA E INICIA O SERVIDOR 
# Expõe uma porta e usa o CMD para tornar a porta do Apache dinâmica
EXPOSE 8080
CMD sh -c "sed -i 's/Listen 80/Listen ${PORT:-8080}/g' /etc/apache2/ports.conf && apache2-foreground"