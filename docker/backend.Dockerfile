FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    zip unzip sqlite3 libsqlite3-dev git \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install

# Crear directorio para la BD (no versionado)
RUN mkdir -p storage/database

# Script simple de inicialización
RUN echo '#!/bin/bash\n\
if [ ! -f storage/database/database.sqlite ]; then\n\
    touch storage/database/database.sqlite\n\
fi\n\
php artisan migrate:fresh --seed --force\n\
php -S 0.0.0.0:8082 -t public' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

EXPOSE 8082

CMD ["/usr/local/bin/start.sh"]