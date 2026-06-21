#!/bin/sh
set -e

# ---- Port dynamique Render (défaut 80) ----
export PORT="${PORT:-80}"

echo ">>> Démarrage sur le port $PORT"

# ---- Générer la config Nginx avec le bon port ----
envsubst '$PORT' < /etc/nginx/nginx.conf.template > /etc/nginx/http.d/default.conf

# ---- Créer les répertoires de stockage si absents ----
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

cd /var/www/html

# ---- Migrations ----
echo ">>> Migration de la base de données..."
php artisan migrate --force

# ---- Seeder (uniquement si la base est vide) ----
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1 | tr -d '[:space:]')
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo ">>> Base vide — seeding des données initiales..."
    php artisan db:seed --force
fi

# ---- Optimisations production ----
echo ">>> Optimisations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">>> Lancement des services..."
exec "$@"
