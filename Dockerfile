FROM php:7.4-fpm-alpine

WORKDIR /var/www/html

RUN set -ex \
  && apk --no-cache add \
    postgresql-dev

RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql
