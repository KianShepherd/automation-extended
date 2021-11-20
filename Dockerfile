FROM php:7.2-apache

COPY . /var/www/html/
COPY php.ini /usr/local/etc/php/

EXPOSE 800

RUN a2enmod headers

RUN apt-get update

RUN apt-get install -y \
    libzip-dev \
    zip \
  && docker-php-ext-configure zip --with-libzip \
  && docker-php-ext-install zip

RUN a2enmod rewrite
