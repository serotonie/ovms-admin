ARG PHP_VERSION=8.4
ARG NODE_VERSION=20

FROM php:${PHP_VERSION} AS base_builder

ARG NODE_VERSION
ENV NODE_VERSION=${NODE_VERSION}

RUN set -eux;   apt-get update &&\
    apt-get upgrade -y --no-install-recommends

RUN set -eux;   apt-get install -y --no-install-recommends \
    zip \
    unzip

RUN set -eux;   docker-php-ext-install -j$(nproc) \
    exif

COPY --from=composer:latest                         /usr/bin/composer               /usr/local/bin/composer

RUN set -eux;   curl -sL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash - 
RUN set -eux;   apt-get install -y nodejs

RUN set -eux;   install -d -o www-data -g www-data /var/www/.npm

WORKDIR /var/www/html
USER www-data

COPY --chown=www-data:www-data                      package.json \                    
                                                    package-lock.json \
                                                    vite.config.js \
                                                    jsconfig.json                   ./ 
RUN set -eux;   npm install

COPY --chown=www-data:www-data                      composer.json \ 
                                                    composer.lock \
                                                    artisan                         ./
COPY --chown=www-data:www-data                      bootstrap                       ./bootstrap/
COPY --chown=www-data:www-data                      routes                          ./routes/
COPY --chown=www-data:www-data                      app                             ./app/
RUN set -eux;   composer install --no-dev

COPY --chown=www-data:www-data                      resources                       ./resources/

RUN set -eux;   npm run build


FROM php:${PHP_VERSION} AS base_runtime

RUN set -eux;   apt-get update && \
    apt-get upgrade -y --no-install-recommends

RUN set -eux;   apt-get install -y --no-install-recommends \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libavif-bin

RUN set -eux;   docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    exif

RUN set -eux;   pecl install redis && \
    rm -rf /tmp/pear && \
    docker-php-ext-enable redis

WORKDIR /var/www/html
USER www-data

COPY --from=base_builder --chown=www-data:www-data  /var/www/html/artisan           ./
COPY --from=base_builder --chown=www-data:www-data  /var/www/html/app               ./app/
COPY --from=base_builder --chown=www-data:www-data  /var/www/html/bootstrap         ./bootstrap/
COPY --from=base_builder --chown=www-data:www-data  /var/www/html/vendor            ./vendor/

COPY --chown=www-data:www-data                      config                          ./config/
COPY --chown=www-data:www-data                      resources/views                 ./resources/views
COPY --chown=www-data:www-data                      routes                          ./routes/
COPY --chown=www-data:www-data                      storage/logs                    ./storage/logs
COPY --chown=www-data:www-data                      storage/app/                    ./storage/app/
COPY --chown=www-data:www-data                      storage/framework/views         ./storage/framework/views
COPY --chown=www-data:www-data                      composer.json                   ./composer.json

FROM base_runtime AS migrator

COPY --chown=www-data:www-data                      database                        ./database

ENTRYPOINT [ "/bin/bash", "-c", "php artisan migrate --force", "--" ]


FROM base_runtime AS scheduler

COPY --chown=www-data:www-data                      storage                         ./storage

ENTRYPOINT [ "/bin/bash", "-c", "while true; do php artisan schedule:run; sleep 60; done;", "--" ]


FROM scheduler AS queue-worker

ENTRYPOINT [ "/bin/bash", "-c", "php artisan queue:work", "--" ]


FROM php:${PHP_VERSION}-fpm AS frontend

RUN set -eux;   apt-get update &&\
    apt-get upgrade -y --no-install-recommends

RUN set -eux;   apt-get install -y --no-install-recommends \
    nginx \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libavif-bin

RUN set -eux;   docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    exif

RUN set -eux;   pecl install redis && \
    rm -rf /tmp/pear && \
    docker-php-ext-enable redis

RUN set -eux;   rm -rf /etc/nginx/conf.d/default.conf
RUN set -eux;   rm -rf /etc/nginx/sites-enabled/default
RUN set -eux;   rm -rf /etc/nginx/sites-available/default

RUN set -eux;   rm -rf /etc/nginx/nginx.conf

RUN set -eux;   mkdir /etc/nginx/certs

COPY --chown=www-data:www-data                      ./nginx/generate-ssl-certs.sh   /opt/

COPY --chown=root:root                              ./nginx/nginx.conf              /etc/nginx/

RUN set -eux;   rm -rf /var/www/html/*

WORKDIR /var/www/html

COPY --from=scheduler --chown=www-data:www-data     /var/www/html                   ../html/

COPY --chown=www-data:www-data                      public                          ./public/

COPY --from=base_builder --chown=www-data:www-data  /var/www/html/public/build      ./public/build/

ENTRYPOINT [    "/bin/bash", \
    "-c", \
    "/opt/generate-ssl-certs.sh; \
    chown -R www-data:www-data storage; \
    su -s /bin/bash -c 'mkdir -p storage/framework/views storage/logs' www-data; \
    su -s /bin/bash -c 'php artisan optimize' www-data; \
    php-fpm -D; \
    nginx -g 'daemon off;'", "--" ]
