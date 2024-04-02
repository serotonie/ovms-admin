FROM php:8 as php_base

RUN apt-get update \
 && apt-get -y upgrade --no-install-recommends \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN pecl channel-update pecl.php.net \
 && pecl install redis \
 && rm -rf /tmp/pear \
 && docker-php-ext-enable redis


FROM php:8-fpm as php-fpm
FROM php_base as fpm_nginx_base
COPY --from=php-fpm / /
RUN apt-get update \
 && apt-get install -y \
    nginx \
    --no-install-recommends \
 && apt-get clean && rm -rf /var/lib/apt/lists/*


FROM php_base as builder

COPY --from=composer/composer:latest-bin /composer /usr/bin/composer
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - 

RUN apt-get update \
 && apt-get install -y \
    unzip \
    zip \
    nodejs \
    --no-install-recommends \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN groupadd --gid 1000 builder \
    && useradd --uid 1000 --gid 1000 -m builder

USER builder
WORKDIR /home/builder

COPY --chown=builder:builder . .

RUN composer install --no-dev && \
    npm install && \
    npm run build


FROM fpm_nginx_base as laravel_frontend

WORKDIR /var/www/html

RUN rm -r /var/www/html/*

COPY --from=builder --chown=www-data:www-data \
    /home/builder/app ./app

COPY --from=builder --chown=www-data:www-data \
    /home/builder/bootstrap ./bootstrap

COPY --from=builder --chown=www-data:www-data \
    /home/builder/config ./config

COPY --from=builder --chown=www-data:www-data \
    /home/builder/public ./public

COPY --from=builder --chown=www-data:www-data \
    /home/builder/resources ./resources

COPY --from=builder --chown=www-data:www-data \
    /home/builder/routes ./routes

COPY --from=builder --chown=www-data:www-data \
    /home/builder/storage ./storage

