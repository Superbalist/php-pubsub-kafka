#!/bin/bash

cd /tmp \
    && mkdir librdkafka \
    && cd librdkafka \
    && git clone https://github.com/edenhill/librdkafka.git . \
    && ./configure \
    && make \
    && sudo make install

if [ $TRAVIS_PHP_VERSION = "7.0" ]
then
    cd /tmp \
        && mkdir php-rdkafka \
        && cd php-rdkafka \
        && git clone https://github.com/arnaud-lb/php-rdkafka.git . \
        && git checkout php7 \
        && phpize \
        && ./configure \
        && make \
        && sudo make install
else
    pecl install channel://pecl.php.net/rdkafka-alpha
fi

echo "extension = rdkafka.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini