<?php

namespace Superbalist\PubSub\Kafka;

use Superbalist\PubSub\PubSubAdapterInterface;
use Superbalist\PubSub\Utils;

class KafkaPubSubAdapter implements PubSubAdapterInterface
{
    /**
     * @var \RdKafka\Producer
     */
    protected $producer;

    /**
     * @var \RdKafka\KafkaConsumer
     */
    protected $consumer;

    /**
     * @param \RdKafka\Producer $producer
     * @param \RdKafka\KafkaConsumer $consumer
     */
    public function __construct(\RdKafka\Producer $producer, \RdKafka\KafkaConsumer $consumer)
    {
        $this->producer = $producer;
        $this->consumer = $consumer;
    }

    /**
     * Return the Kafka producer.
     *
     * @return \RdKafka\Producer
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Return the Kafka consumer.
     *
     * @return \RdKafka\KafkaConsumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * Subscribe a handler to a channel.
     *
     * @param string $channel
     * @param callable $handler
     *
     * @throws \Exception
     */
    public function subscribe($channel, callable $handler)
    {
        $this->consumer->subscribe([$channel]);

        $isSubscriptionLoopActive = true;

        while ($isSubscriptionLoopActive) {
            $message = $this->consumer->consume(120 * 1000);

            if ($message === null) {
                continue;
            }

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $payload = Utils::unserializeMessagePayload($message->payload);

                    if ($payload === 'unsubscribe') {
                        $isSubscriptionLoopActive = false;
                    } else {
                        call_user_func($handler, $payload);
                    }

                    $this->consumer->commitAsync($message);

                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
            }
        }
    }

    /**
     * Publish a message to a channel.
     *
     * @param string $channel
     * @param mixed $message
     */
    public function publish($channel, $message)
    {
        $topic = $this->producer->newTopic($channel);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, Utils::serializeMessage($message));
    }

    /**
     * Publish multiple messages to a channel.
     *
     * @param string $channel
     * @param array $messages
     */
    public function publishBatch($channel, array $messages)
    {
        foreach ($messages as $message) {
            $this->publish($channel, $message);
        }
    }
}
