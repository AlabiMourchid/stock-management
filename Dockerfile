# ============================================================
# Amira — Gestion Stock  |  PHP 8.2 + Nginx + PostgreSQL
# ============================================================

FROM php:8.2-fpm-alpine

# ---- Paquets système ----
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libpng-dev \
    libzip-dev \
    gettext \
    curl \
    zip \
    unzip

# ---- Extensions PHP ----
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    bcmath \
    gd \
    zip \
    opcache

# ---- OPcache (production) ----
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=0'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# ---- Composer ----
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ---- Dépendances PHP (couche de cache séparée) ----
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --no-interaction

# ---- Code applicatif ----
COPY . .

# ---- Autoloader optimisé ----
RUN composer dump-autoload --optimize

# ---- Permissions ----
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# ---- Config Docker ----
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/supervisord.conf    /etc/supervisord.conf
COPY docker/entrypoint.sh       /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
