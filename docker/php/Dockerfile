FROM php:7.4-fpm

RUN apt-get update \
    && apt-get install -y libssl-dev \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb