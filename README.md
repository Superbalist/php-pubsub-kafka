# php-pubsub-kafka

A Kafka adapter for the [php-pubsub](https://github.com/Superbalist/php-pubsub) package.

[![Author](http://img.shields.io/badge/author-@superbalist-blue.svg?style=flat-square)](https://twitter.com/superbalist)
[![Build Status](https://img.shields.io/travis/Superbalist/php-pubsub-kafka/master.svg?style=flat-square)](https://travis-ci.org/Superbalist/php-pubsub-kafka)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/superbalist/php-pubsub-kafka.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-pubsub-kafka)
[![Total Downloads](https://img.shields.io/packagist/dt/superbalist/php-pubsub-kafka.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-pubsub-kafka)


## Installation

1. Install [librdkafka c library](https://github.com/edenhill/librdkafka) (Debian/Ubuntu)

    ```bash
    $ sudo apt-get install librdkafka-dev
    ```
2. Install the [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) PECL extension

    **PHP5**
    ```bash
    $ sudo pecl install channel://pecl.php.net/rdkafka-alpha
    ```
    
    **PHP7**
    ```bash
    $ cd /tmp
    $ mkdir php-rdkafka
    $ cd php-rdkafka
    $ git clone https://github.com/arnaud-lb/php-rdkafka.git .
    $ git checkout php7
    $ phpize
    $ ./configure
    $ make
    $ make install
    ```
    
3. Add the following to your php.ini file to enable the php-rdkafka extension
    `extension=rdkafka.so`
    
4. `composer require superbalist/php-pubsub-kafka`
    
## Usage

```php
// use this topic config for both the producer and consumer
$topicConfig = new \RdKafka\TopicConf();
$topicConfig->set('auto.offset.reset', 'smallest');
$topicConfig->set('auto.commit.interval.ms', 300);

// create producer
$producer = new \RdKafka\Producer();
$producer->addBrokers('127.0.0.1');

// create consumer
// see https://arnaud-lb.github.io/php-rdkafka/phpdoc/rdkafka.examples-high-level-consumer.html
$config = new \RdKafka\Conf();
$config->set('group.id', 'php-pubsub');

$consumer = new \RdKafka\Consumer($config);
$consumer->addBrokers('127.0.0.1');

$adapter = new \Superbalist\PubSub\Kafka\KafkaPubSubAdapter($producer, $consumer, $topicConfig);

// consume messages
// note: this is a blocking call
$adapter->subscribe('my_channel', function ($message) {
    var_dump($message);
});

// publish messages
$adapter->publish('my_channel', 'HELLO WORLD');
$adapter->publish('my_channel', json_encode(['hello' => 'world']));
$adapter->publish('my_channel', 1);
$adapter->publish('my_channel', false);
```

## Examples

The library comes with [examples](examples) for the adapter and a [Dockerfile](Dockerfile) for
running the example scripts.

Run `make up`.

You will start at a `bash` prompt in the `/opt/php-pubsub` directory.

If you need another shell to publish a message to a blocking consumer, you can run `docker-compose run php-pubsub-kafka /bin/bash`

To run the examples:
```bash
$ php examples/KafkaConsumerExample.php
$ php examples/KafkaPublishExample.php (in a separate shell)
```
