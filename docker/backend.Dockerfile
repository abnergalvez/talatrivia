FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    zip unzip sqlite3 libsqlite3-dev git \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install
RUN mkdir -p database && touch database/database.sqlite

EXPOSE 8082

CMD ["php", "-S", "0.0.0.0:8082", "-t", "public"]
