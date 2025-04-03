FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev zip unzip git curl \
    && docker-php-ext-install pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www

#RUN composer install


CMD ["php-fpm"]