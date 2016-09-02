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
     * @var \RdKafka\Consumer
     */
    protected $consumer;

    /**
     * @var \RdKafka\TopicConf
     */
    protected $topicConfig;

    /**
     * @var mixed
     */
    protected $consumerOffset;

    /**
     * @param \RdKafka\Producer $producer
     * @param \RdKafka\Consumer $consumer
     * @param \RdKafka\TopicConf $topicConfig
     * @param mixed $consumerOffset The offset at which to start consumption
     *  (RD_KAFKA_OFFSET_BEGINNING, RD_KAFKA_OFFSET_END, RD_KAFKA_OFFSET_STORED)
     */
    public function __construct(
        \RdKafka\Producer $producer,
        \RdKafka\Consumer $consumer,
        \RdKafka\TopicConf $topicConfig,
        $consumerOffset = RD_KAFKA_OFFSET_END
    ) {
        $this->producer = $producer;
        $this->consumer = $consumer;
        $this->topicConfig = $topicConfig;
        $this->consumerOffset = $consumerOffset;
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
     * @return \RdKafka\Consumer
     */
    public function getConsumer()
    {
        return $this->consumer;
    }

    /**
     * Return the Kafka TopicConfig.
     *
     * @return \RdKafka\TopicConf
     */
    public function getTopicConfig()
    {
        return $this->topicConfig;
    }

    /**
     * Return the Kafka consumer offset at which `subscribe()` calls begin consumption.
     *
     * @return mixed
     */
    public function getConsumerOffset()
    {
        return $this->consumerOffset;
    }

    /**
     * Set the Kafka consumer offset at which `subscribe()` calls begin consumption.
     *
     * This can be one of `RD_KAFKA_OFFSET_BEGINNING`, `RD_KAFKA_OFFSET_END` or `RD_KAFKA_OFFSET_STORED`
     *
     * @param mixed $consumerOffset
     */
    public function setConsumerOffset($consumerOffset)
    {
        $this->consumerOffset = $consumerOffset;
    }

    /**
     * Subscribe a handler to a channel.
     *
     * @param string $channel
     * @param callable $handler
     * @throws \Exception
     */
    public function subscribe($channel, callable $handler)
    {
        $topic = $this->consumer->newTopic($channel, $this->topicConfig);

        $topic->consumeStart(0, $this->consumerOffset);

        $isSubscriptionLoopActive = true;

        while ($isSubscriptionLoopActive) {
            $message = $topic->consume(0, 1000);

            if ($message === null) {
                continue;
            }

            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $payload = Utils::unserializeMessagePayload($message->payload);

                    if ($payload === 'unsubscribe') {
                        $isSubscriptionLoopActive = false;
                        break;
                    }

                    call_user_func($handler, $payload);
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
        $topic = $this->producer->newTopic($channel, $this->topicConfig);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, Utils::serializeMessage($message));
    }
}
