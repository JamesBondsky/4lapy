ARG PHP_VERSION=7.1
ARG NGINX_VERSION=1.14

# SSL keys stage
#FROM alpine:latest as api_platform_ssl
#
#RUN apk add --no-cache openssl
#
#RUN openssl genrsa -des3 -passout pass:NotSecure -out cert.pass.key 2048
#RUN openssl rsa -passin pass:NotSecure -in cert.pass.key -out cert.key
#RUN rm cert.pass.key
#RUN openssl req -new -passout pass:NotSecure -key cert.key -out cert.csr \
#    -subj '/C=SS/ST=SS/L=Gotham City/O=API Platform Dev/CN=4lapy.local.articul.ru'
#RUN openssl x509 -req -sha256 -days 365 -in cert.csr -signkey cert.key -out cert.crt


# php-fpm
FROM php:${PHP_VERSION}-fpm AS php
MAINTAINER Articul.ru <ps@articul.ru>

WORKDIR /application

COPY ./docker/php/custom.ini /usr/local/etc/php/conf.d/50-custom.ini
COPY ./docker/php-fpm/dev.application.conf /usr/local/etc/php-fpm.d/dev.application.conf

RUN set -ex \
   && apt-get update && apt-get install -y \
   git \
   libfreetype6-dev \
   libicu-dev \
   libjpeg-dev \
   libmcrypt-dev \
   libpng-dev \
   libxml2-dev \
   libmemcached-dev \
   libz-dev \
   unzip \
   && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ \
   && docker-php-ext-install bcmath calendar gd iconv intl mbstring mysqli opcache pcntl pdo pdo_mysql soap sockets sysvmsg sysvsem sysvshm xml zip \
   && curl -L -o /tmp/memcached.tar.gz "https://github.com/php-memcached-dev/php-memcached/archive/php7.tar.gz" \
   && mkdir -p /usr/src/php/ext/memcached \
   && tar -C /usr/src/php/ext/memcached -zxvf /tmp/memcached.tar.gz --strip 1 \
   && docker-php-ext-configure memcached \
   && docker-php-ext-install memcached \
   && rm /tmp/memcached.tar.gz \
   && curl -L -o /tmp/php7.zip "https://github.com/websupport-sk/pecl-memcache/archive/php7.zip" \
   && unzip /tmp/php7.zip -d /tmp \
   && cd /tmp/pecl-memcache-php7 \
   && phpize \
   && ./configure \
   && make && make install \
   && echo "extension = memcache.so" > /usr/local/etc/php/conf.d/php-ext-memcache.ini \
   && rm /tmp/php7.zip \
   && pecl install redis \
   && docker-php-ext-enable redis \
   && pecl install xdebug \
   && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
   && apt-get clean

VOLUME /var/log/php

RUN mkdir var
RUN mkdir var/cache
RUN chmod -R 777 var
RUN chown -R www-data:www-data var

#COPY ./composer.json /application/composer.json
#COPY ./composer.lock /application/composer.lock
#
#RUN mkdir /root/.ssh/
#RUN touch /root/.ssh/known_hosts
#RUN ssh-keyscan gitea.articul.ru >> /root/.ssh/known_hosts
#RUN ssh-keyscan bitbucket.org >> /root/.ssh/known_hosts
#COPY ./docker/id_rsa /root/.ssh/id_rsa
#
#COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
#RUN set -eux; \
#	composer install --ignore-platform-reqs --prefer-dist --no-scripts --no-progress --no-suggest; \
#	composer clear-cache
#
#RUN cd /application/vendor/4lapy/bitrix && git pull && cd /application
#RUN chmod -R 777 /application/vendor/adv/yandex-market-api
#RUN chown -R www-data:www-data /application/vendor/adv/yandex-market-api
#
#RUN ln -s /application/vendor/4lapy/bitrix /application/common

CMD ["php-fpm"]


# nginx
FROM nginx:${NGINX_VERSION} AS nginx

RUN mkdir -p /etc/nginx/ssl/
#COPY --from=api_platform_ssl cert.key cert.crt /etc/nginx/ssl/
COPY ./docker/nginx/ssl /etc/nginx/ssl
COPY ./docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY ./docker/nginx/conf.d /etc/nginx/conf.d
COPY ./docker/nginx/modules-enabled /etc/nginx/modules-enabled

VOLUME /var/log/nginx/
WORKDIR /application


# redis
FROM redis:5-alpine AS redis

COPY ./docker/redis /usr/local/etc/redis


# elasticsearch
FROM elasticsearch:5.6.8 AS elasticsearch

COPY ./docker/elasticsearch/config /usr/share/elasticsearch/config

RUN /usr/share/elasticsearch/bin/elasticsearch-plugin install analysis-icu
RUN /usr/share/elasticsearch/bin/elasticsearch-plugin install analysis-phonetic


# rabbitmq
FROM rabbitmq:latest AS rabbitmq

RUN rabbitmq-plugins enable rabbitmq_management
