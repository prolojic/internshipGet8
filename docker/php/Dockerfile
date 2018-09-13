FROM mileschou/phalcon:7.2-fpm

ENV SYMFONY_ENV=prod
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update
RUN apt-get install -y git zip unzip libzip-dev

RUN  curl -sS https://getcomposer.org/installer | php -- \
                --install-dir=/usr/local/bin \
                --filename=composer

RUN pecl install -o -f redis zip \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis \
&&  docker-php-ext-enable zip
