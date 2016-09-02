<?php

include __DIR__ . '/../vendor/autoload.php';

// use this topic config for both the producer and consumer
$topicConfig = new \RdKafka\TopicConf();
$topicConfig->set('auto.offset.reset', 'smallest');
$topicConfig->set('auto.commit.interval.ms', 300);

// create producer
$producer = new \RdKafka\Producer();
$producer->addBrokers('kafka');

// create consumer
// see https://arnaud-lb.github.io/php-rdkafka/phpdoc/rdkafka.examples-high-level-consumer.html
$config = new \RdKafka\Conf();
$config->set('group.id', 'php-pubsub');

$consumer = new \RdKafka\Consumer($config);
$consumer->addBrokers('kafka');

$adapter = new \Superbalist\PubSub\Kafka\KafkaPubSubAdapter($producer, $consumer, $topicConfig);

$adapter->publish('my_channel', 'HELLO WORLD');
$adapter->publish('my_channel', json_encode(['hello' => 'world']));
$adapter->publish('my_channel', 1);
$adapter->publish('my_channel', false);
