FROM php:8.3-cli

RUN apt-get update && apt-get install -y     git unzip curl libzip-dev unixodbc-dev gnupg

RUN docker-php-ext-install pdo zip

WORKDIR /var/www

COPY . .

CMD php artisan serve --host=0.0.0.0 --port=8000