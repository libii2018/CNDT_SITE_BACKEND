FROM php:8.3-apache

# Extensions PHP (pdo_mysql) + outils nécessaires à Composer (unzip, git)
RUN apt-get update && apt-get install -y --no-install-recommends unzip git \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# DocumentRoot Apache → /public de Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && a2enmod rewrite

WORKDIR /var/www/html

# Composer (image officielle)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Code applicatif (.dockerignore exclut vendor, logs, .git, sqlite local)
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && chown -R www-data:www-data storage bootstrap/cache \
    && { \
      echo 'opcache.revalidate_freq=30'; \
      echo 'opcache.validate_timestamps=1'; \
      echo 'realpath_cache_size=4096K'; \
      echo 'realpath_cache_ttl=600'; \
    } > /usr/local/etc/php/conf.d/opcache-dev.ini

EXPOSE 80

# Migrations au démarrage (jamais de seed automatique :
# le seeding est opt-in via DB_SEED=true, et le seeder est
# non-destructif — il n'écrase aucune donnée existante),
# puis serveur Apache
CMD ["sh", "-c", "php artisan storage:link >/dev/null 2>&1 || true; php artisan migrate --force && { [ \"${DB_SEED:-false}\" = \"true\" ] && php artisan db:seed --force; }; apache2-foreground"]
