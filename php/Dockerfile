FROM php:8-fpm
RUN apt-get -y update \
    && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install mysqli pdo_mysql intl
