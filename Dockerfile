FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libicu-dev \
    libzip-dev zip unzip git curl autoconf pkg-config\
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . /var/www

#RUN composer install
RUN composer install --no-dev --optimize-autoloader

CMD symfony server:start --port=8000 --allow-http --no-tls