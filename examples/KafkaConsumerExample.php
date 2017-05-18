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
$conf->setDefaultTopicConf($topicConf);

$consumer = new \RdKafka\KafkaConsumer($conf);

// create producer
$producer = new \RdKafka\Producer();
$producer->addBrokers($broker);

$adapter = new \Superbalist\PubSub\Kafka\KafkaPubSubAdapter($producer, $consumer);

$adapter->subscribe('my_channel', function ($message) {
    var_dump($message);
});
