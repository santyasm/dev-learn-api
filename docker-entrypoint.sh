set -e

echo "Aguardando banco de dados..."
until pg_isready -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}"; do
  echo "Banco de dados ainda não está pronto. Tentando novamente em 2s..."
  sleep 2
done
echo "Banco de dados pronto!"

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ -z "$(php artisan key:generate --show)" ]; then
    php artisan key:generate --force
fi

echo "Rodando migrations..."
php artisan migrate --force

# Permissões
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Iniciando servidor PHP..."
exec php -S 0.0.0.0:${PORT:-8080} -t public
