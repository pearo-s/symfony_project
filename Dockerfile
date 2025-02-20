FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

RUN composer install

CMD ["php-fpm"]