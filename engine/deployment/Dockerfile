## Composer
FROM composer:latest AS composer

COPY . /app/

ENV APP_ENV prod
RUN composer install --no-dev --optimize-autoloader

RUN php bin/console cache:warmup

# NodeJS (Webpack Encore)
FROM node:8 AS node

COPY --from=composer /app /app
WORKDIR /app

RUN yarn install
RUN yarn run build

## Build
FROM php:7.2-apache AS apache

ENV APP_ENV prod

RUN pecl install apcu
RUN echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini

RUN docker-php-ext-install  opcache \
 && a2enmod rewrite

RUN rm /etc/apache2/sites-enabled/000-default.conf
COPY engine/deployment/website.conf /etc/apache2/sites-enabled/

COPY --from=node /app /var/www/html
COPY . /var/www/html

WORKDIR /var/www/html

RUN chmod -R 775 .
RUN chown -R www-data:www-data .

USER www-data

EXPOSE 8080

ENTRYPOINT []
CMD sed -i "s/80/8080/g" /etc/apache2/sites-enabled/website.conf /etc/apache2/ports.conf && \
    docker-php-entrypoint apache2-foreground
