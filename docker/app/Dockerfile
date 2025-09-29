FROM ubuntu:noble

RUN apt-get update && \
    apt-get install -y software-properties-common && \
    add-apt-repository ppa:ondrej/php && \
    apt-get upgrade -y

RUN apt-get update && \
    apt-get install -y \
    php8.4-fpm php8.4-common php8.4-ctype php8.4-simplexml php8.4-tokenizer php8.4-pdo php8.4-sqlite3 php8.4-cli \
    php8.4-mbstring php8.4-xml php8.4-zip php8.4-intl php8.4-curl \
    git \
    supervisor \
    nginx \
    vim

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/* && rm -R /var/www/*

WORKDIR /var/www

RUN mkdir ./var

COPY ./bin/console ./bin/console
COPY ./config ./config
COPY ./migrations ./migrations
COPY ./public ./public
COPY ./src ./src
COPY ./vendor ./vendor
COPY ./composer.json ./composer.json
COPY ./composer.lock ./composer.lock

COPY ./docker/app/nginx.conf /etc/nginx/nginx.conf

COPY ./docker/app/php.ini ./docker/app/php.ini
COPY ./docker/app/php-fpm.conf ./docker/app/php-fpm.conf
COPY ./docker/app/docker-entrypoint.sh /docker-entrypoint.sh

COPY ./docker/app/supervisord.conf ./docker/app/supervisord.conf

COPY ./meta.json ./meta.json

RUN chmod +x /docker-entrypoint.sh

RUN usermod -s /bin/bash www-data

RUN chown www-data:www-data ./var

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["app"]
