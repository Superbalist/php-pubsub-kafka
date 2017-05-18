# php-pubsub-kafka

A Kafka adapter for the [php-pubsub](https://github.com/Superbalist/php-pubsub) package.

[![Author](http://img.shields.io/badge/author-@superbalist-blue.svg?style=flat-square)](https://twitter.com/superbalist)
[![Build Status](https://img.shields.io/travis/Superbalist/php-pubsub-kafka/master.svg?style=flat-square)](https://travis-ci.org/Superbalist/php-pubsub-kafka)
[![StyleCI](https://styleci.io/repos/67255347/shield?branch=master)](https://styleci.io/repos/67255347)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/superbalist/php-pubsub-kafka.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-pubsub-kafka)
[![Total Downloads](https://img.shields.io/packagist/dt/superbalist/php-pubsub-kafka.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-pubsub-kafka)


## Installation

1. Install [librdkafka c library](https://github.com/edenhill/librdkafka)

    ```bash
    $ cd /tmp
    $ mkdir librdkafka
    $ cd librdkafka
    $ git clone https://github.com/edenhill/librdkafka.git .
    $ ./configure
    $ make
    $ make install
    ```
2. Install the [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) PECL extension

    ```bash
    $ pecl install rdkafka
    ```
    
3. Add the following to your php.ini file to enable the php-rdkafka extension
    `extension=rdkafka.so`
    
4. `composer require superbalist/php-pubsub-kafka`
    
## Usage

```php
// create consumer
$topicConf = new \RdKafka\TopicConf();
$topicConf->set('auto.offset.reset', 'largest');

$conf = new \RdKafka\Conf();
$conf->set('group.id', 'php-pubsub');
$conf->set('metadata.broker.list', '127.0.0.1');
$conf->set('enable.auto.commit', 'false');
$conf->set('offset.store.method', 'broker');
$conf->set('socket.blocking.max.ms', 50);
$conf->setDefaultTopicConf($topicConf);

$consumer = new \RdKafka\KafkaConsumer($conf);

// create producer
$conf = new \RdKafka\Conf();
$conf->set('socket.blocking.max.ms', 50);
$conf->set('queue.buffering.max.ms', 20);

$producer = new \RdKafka\Producer($conf);
$producer->addBrokers('127.0.0.1');

$adapter = new \Superbalist\PubSub\Kafka\KafkaPubSubAdapter($producer, $consumer);

// consume messages
// note: this is a blocking call
$adapter->subscribe('my_channel', function ($message) {
    var_dump($message);
});

// publish messages
$adapter->publish('my_channel', 'HELLO WORLD');
$adapter->publish('my_channel', ['hello' => 'world']);
$adapter->publish('my_channel', 1);
$adapter->publish('my_channel', false);

// publish multiple messages
$messages = [
    ['hello' => 'world'],
    'lorem ipsum',
];
$adapter->publishBatch('my_channel', $messages);
```

## Examples

The library comes with [examples](examples) for the adapter and a [Dockerfile](Dockerfile) for
running the example scripts.

Run `make up`.

You will start at a `bash` prompt in the `/opt/php-pubsub` directory.

If you need another shell to publish a message to a blocking consumer, you can run `make shell`

To run the examples:
```bash
$ php examples/KafkaConsumerExample.php
$ php examples/KafkaPublishExample.php (in a separate shell)
```
