FROM php:7.2-cli-buster

# Buster is EOL: its apt repositories moved to archive.debian.org.
RUN sed -i -e 's|deb.debian.org|archive.debian.org|g' -e 's|security.debian.org|archive.debian.org|g' -e '/buster-updates/d' /etc/apt/sources.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libzip-dev \
    && docker-php-ext-install pdo_mysql intl zip opcache \
    && apt-get clean

COPY --from=composer:1 /usr/bin/composer /usr/local/bin/composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer2

WORKDIR /app
