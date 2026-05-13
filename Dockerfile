FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    curl \
    zip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    unixodbc-dev \
    gnupg \
    apt-transport-https \
    lsb-release \
    ca-certificates \
    g++ \
    make \
    autoconf \
    pkg-config \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor > /usr/share/keyrings/microsoft.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
       msodbcsql18 \
       mssql-tools \
       unixodbc-dev \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

RUN docker-php-ext-install -j$(nproc) mbstring bcmath zip xml

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN if [ ! -f .env ] && [ -f .env.example ]; then cp .env.example .env; fi
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
RUN php artisan key:generate --force

RUN chown -R www-data:www-data /var/www

EXPOSE 8000

CMD ["bash", "-lc", "php artisan serve --host=0.0.0.0 --port=8000"]