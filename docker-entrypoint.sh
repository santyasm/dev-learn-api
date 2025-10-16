set -e

php artisan migrate --force

apache2-foreground
