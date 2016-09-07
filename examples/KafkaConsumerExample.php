<?php

include __DIR__ . '/../vendor/autoload.php';

// create consumer
$topicConf = new \RdKafka\TopicConf();
$topicConf->set('auto.offset.reset', 'smallest');

$conf = new \RdKafka\Conf();
$conf->set('group.id', 'php-pubsub');
$conf->set('metadata.broker.list', 'kafka');
$conf->set('enable.auto.commit', 'false');
$conf->set('offset.store.method', 'broker');
$conf->setDefaultTopicConf($topicConf);

$consumer = new \RdKafka\KafkaConsumer($conf);

// create producer
$producer = new \RdKafka\Producer();
$producer->addBrokers('kafka');

$adapter = new \Superbalist\PubSub\Kafka\KafkaPubSubAdapter($producer, $consumer);

$adapter->subscribe('my_channel', function ($message) {
    var_dump($message);
});
