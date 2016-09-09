#!/bin/bash

set -e

PHP_VERSION=$(phpenv global)

cd /tmp \
    && mkdir librdkafka \
    && cd librdkafka \
    && git clone https://github.com/edenhill/librdkafka.git . \
    && ./configure \
    && make \
    && sudo make install

if [[ "$PHP_VERSION" == "7.1" ]] || [[ "$PHP_VERSION" == "nightly" ]]
then
    cd /tmp \
        && mkdir php-rdkafka \
        && cd php-rdkafka \
        && git clone https://github.com/arnaud-lb/php-rdkafka.git \
        && phpize \
        && ./configure \
        && make \
        && sudo make install
elif [[ "$PHP_VERSION" == "7.0" ]]
then
    pecl install channel://pecl.php.net/rdkafka-beta
else
    pecl install channel://pecl.php.net/rdkafka-1.0.0
fi

echo "extension = rdkafka.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini