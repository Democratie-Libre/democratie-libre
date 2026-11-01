FROM php:8.5-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libzip-dev \
    && docker-php-ext-install pdo_mysql intl zip \
    && apt-get clean

WORKDIR /app
