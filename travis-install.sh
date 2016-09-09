#!/bin/bash

set -e

cd /tmp \
    && mkdir librdkafka \
    && cd librdkafka \
    && git clone https://github.com/edenhill/librdkafka.git . \
    && ./configure \
    && make \
    && sudo make install

if [[ "$TRAVIS_PHP_VERSION" =~ ^7.* ]]
then
    pecl install channel://pecl.php.net/rdkafka-beta
else
    pecl install channel://pecl.php.net/rdkafka-1.0.0
fi

echo "extension = rdkafka.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini