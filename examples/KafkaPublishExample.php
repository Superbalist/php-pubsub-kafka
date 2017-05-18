<?php

include __DIR__ . '/../vendor/autoload.php';

$broker = getenv('KAFKA_BROKER');

// create consumer
$topicConf = new \RdKafka\TopicConf();
$topicConf->set('auto.offset.reset', 'largest');

$conf = new \RdKafka\Conf();
$conf->set('group.id', 'php-pubsub');
$conf->set('metadata.broker.list', $broker);
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
$producer->addBrokers($broker);

$adapter = new \Superbalist\PubSub\Kafka\KafkaPubSubAdapter($producer, $consumer);

for ($x = 0; $x < 10; $x++) {
    $adapter->publish('my_channel', $x);
}
