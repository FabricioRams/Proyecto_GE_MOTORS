FROM php:8.2-fpm

LABEL maintainer="G&E Motors <dev@gemmotors.com>"

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd pgsql pdo_pgsql

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www

RUN chown -R www-data:www-data /var/www

EXPOSE 8000

CMD ["php-fpm"]