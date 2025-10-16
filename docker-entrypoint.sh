#!/bin/sh
set -e

# Define APP_KEY se não existir
if [ -z "$APP_KEY" ]; then
    echo "Gerando APP_KEY..."
    php artisan key:generate --force
fi

# Executa migrations (ignora erros se já rodadas)
echo "Rodando migrations..."
# php artisan migrate --force || true
php artisan migrate:fresh --force || true


# Permissões (apenas para garantir)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Executa comando padrão (Apache)
exec "$@"