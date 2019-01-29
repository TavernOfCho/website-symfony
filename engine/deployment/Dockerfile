FROM ubuntu:bionic
#LABEL maintainer="LiveXP <dev@livexp.fr>"

ENV DEBIAN_FRONTEND="noninteractive"

ENV COMPOSER_HOME="/tmp" \
    COMPOSER_ALLOW_SUPERUSER=1 \
    LDAPTLS_REQCERT="never"

ENV APP_ENV="dev"

# DEPENDENCIES

RUN apt-get update -q && \
    apt-get install --no-install-recommends -qy \
        ca-certificates \
        curl \
        apache2 \
        libapache2-mod-php7.2 \
        php7.2 \
        php7.2-curl \
        php7.2-ldap \
        php7.2-apcu \
        php7.2-bz2 \
        php7.2-gd \
        php7.2-gmp \
        php7.2-intl \
        php7.2-yaml \
        php7.2-common \
        php7.2-json \
        php7.2-mbstring \
        php7.2-phar \
        php7.2-xml \
        php7.2-zip \
        php7.2-xdebug

# SYSTEM CONFIGURATION

RUN cp /usr/share/zoneinfo/Europe/Paris /etc/localtime && echo "Europe/Paris" > /etc/timezone

# APACHE CONFIGURATION

RUN rm /etc/apache2/sites-enabled/000-default.conf
COPY engine/deployment/apache2-foreground /usr/local/bin/
COPY engine/deployment/website.conf /etc/apache2/sites-enabled/

RUN a2enmod rewrite

# PHP CONFIGURATION

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# NODEJS CONFIGURATION

RUN apt-get install -y nodejs npm
RUN npm install -g yarn


# PROJECT CONFIGURATION

WORKDIR /app
COPY . /app

RUN chown -R www-data:www-data /app
USER www-data

#RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-suggest
RUN composer install --no-interaction --no-progress --no-suggest
RUN rm -rf node_modules
RUN yarn install
RUN yarn run build

# CLEAN UP

USER root

RUN rm -rf /var/lib/apt/lists/* && \
    apt-get autoremove -y && \
    apt-get clean

RUN rm -rf /tmp/* && \
    rm -rf /app/engine

# ENTRYPOINT

EXPOSE 80
CMD ["/usr/local/bin/apache2-foreground"]
