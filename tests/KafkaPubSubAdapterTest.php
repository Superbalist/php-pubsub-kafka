<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase;
use Superbalist\PubSub\Kafka\KafkaPubSubAdapter;
use Tests\Mocks\MockKafkaErrorMessage;

if (!extension_loaded('rdkafka')) {
    define('RD_KAFKA_OFFSET_BEGINNING', 0);
    define('RD_KAFKA_OFFSET_END', 1);
    define('RD_KAFKA_OFFSET_STORED', 2);

    define('RD_KAFKA_PARTITION_UA', 0);

    define('RD_KAFKA_RESP_ERR_NO_ERROR', 0);
    define('RD_KAFKA_RESP_ERR__PARTITION_EOF', 1);
    define('RD_KAFKA_RESP_ERR__TIMED_OUT', 2);
}

class KafkaPubSubAdapterTest extends TestCase
{
    public function testGetProducer()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);
        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);
        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);
        $this->assertSame($producer, $adapter->getProducer());
    }

    public function testGetConsumer()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);
        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);
        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);
        $this->assertSame($consumer, $adapter->getConsumer());
    }

    public function testGetTopicConfig()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);
        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);
        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);
        $this->assertSame($topicConfig, $adapter->getTopicConfig());
    }

    public function testGetConsumerOffsetDefaultIsEnd()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);
        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);
        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);
        $this->assertEquals(RD_KAFKA_OFFSET_END, $adapter->getConsumerOffset());
    }

    public function testGetSetConsumerOffset()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);
        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);
        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig, RD_KAFKA_OFFSET_STORED);
        $this->assertSame(RD_KAFKA_OFFSET_STORED, $adapter->getConsumerOffset());

        $adapter->setConsumerOffset(RD_KAFKA_OFFSET_BEGINNING);
        $this->assertEquals(RD_KAFKA_OFFSET_BEGINNING, $adapter->getConsumerOffset());
    }

    public function testSubscribeWithNullMessage()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $topic = Mockery::mock(\RdKafka\Topic::class);
        $topic->shouldReceive('consumeStart')
            ->withArgs([
                0,
                RD_KAFKA_OFFSET_END
            ])
            ->once();

        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturnNull()
            ->once();

        // we need this to kill the infinite loop so the test can finish
        $unsubscribeMessage = new \stdClass();
        $unsubscribeMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $unsubscribeMessage->payload = 'unsubscribe';
        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturn($unsubscribeMessage)
            ->once();

        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);

        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $consumer->shouldReceive('newTopic')
            ->withArgs([
                'channel_name',
                $topicConfig
            ])
            ->once()
            ->andReturn($topic);

        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldNotReceive('handle');

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }

    public function testSubscribeWithPartitionEofErrorCode()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $topic = Mockery::mock(\RdKafka\Topic::class);
        $topic->shouldReceive('consumeStart')
            ->withArgs([
                0,
                RD_KAFKA_OFFSET_END
            ])
            ->once();

        $message = new \stdClass();
        $message->err = RD_KAFKA_RESP_ERR__PARTITION_EOF;
        $message->payload = null;
        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturn($message)
            ->once();

        // we need this to kill the infinite loop so the test can finish
        $unsubscribeMessage = new \stdClass();
        $unsubscribeMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $unsubscribeMessage->payload = 'unsubscribe';
        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturn($unsubscribeMessage)
            ->once();

        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);

        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $consumer->shouldReceive('newTopic')
            ->withArgs([
                'channel_name',
                $topicConfig
            ])
            ->once()
            ->andReturn($topic);

        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldNotReceive('handle');

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }

    public function testSubscribeWithTimedOutErrorCode()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $topic = Mockery::mock(\RdKafka\Topic::class);
        $topic->shouldReceive('consumeStart')
            ->withArgs([
                0,
                RD_KAFKA_OFFSET_END
            ])
            ->once();

        $message = new \stdClass();
        $message->err = RD_KAFKA_RESP_ERR__TIMED_OUT;
        $message->payload = null;
        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturn($message)
            ->once();

        // we need this to kill the infinite loop so the test can finish
        $unsubscribeMessage = new \stdClass();
        $unsubscribeMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $unsubscribeMessage->payload = 'unsubscribe';
        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturn($unsubscribeMessage)
            ->once();

        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);

        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $consumer->shouldReceive('newTopic')
            ->withArgs([
                'channel_name',
                $topicConfig
            ])
            ->once()
            ->andReturn($topic);

        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldNotReceive('handle');

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }

    public function testSubscribeWithMessagePayload()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $topic = Mockery::mock(\RdKafka\Topic::class);
        $topic->shouldReceive('consumeStart')
            ->withArgs([
                0,
                RD_KAFKA_OFFSET_END
            ])
            ->once();

        $message = new \stdClass();
        $message->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $message->payload = 'a:1:{s:5:"hello";s:5:"world";}';
        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturn($message)
            ->once();

        // we need this to kill the infinite loop so the test can finish
        $unsubscribeMessage = new \stdClass();
        $unsubscribeMessage->err = RD_KAFKA_RESP_ERR_NO_ERROR;
        $unsubscribeMessage->payload = 'unsubscribe';
        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturn($unsubscribeMessage)
            ->once();

        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);

        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $consumer->shouldReceive('newTopic')
            ->withArgs([
                'channel_name',
                $topicConfig
            ])
            ->once()
            ->andReturn($topic);

        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldReceive('handle')
            ->with(['hello' => 'world'])
            ->once();

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }

    public function testSubscribeWithErrorThrowsException()
    {
        $producer = Mockery::mock(\RdKafka\Producer::class);

        $topic = Mockery::mock(\RdKafka\Topic::class);
        $topic->shouldReceive('consumeStart')
            ->withArgs([
                0,
                RD_KAFKA_OFFSET_END
            ])
            ->once();

        $topic->shouldReceive('consume')
            ->withArgs([
                0,
                1000
            ])
            ->andReturn(new MockKafkaErrorMessage())
            ->once();

        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);

        $consumer = Mockery::mock(\RdKafka\Consumer::class);
        $consumer->shouldReceive('newTopic')
            ->withArgs([
                'channel_name',
                $topicConfig
            ])
            ->once()
            ->andReturn($topic);

        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);

        $handler1 = Mockery::mock(\stdClass::class);
        $handler1->shouldNotReceive('handle');

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(1234);
        $this->expectExceptionMessage('This is an error message.');

        $adapter->subscribe('channel_name', [$handler1, 'handle']);
    }

    public function testPublish()
    {
        $topic = Mockery::mock(\RdKafka\Topic::class);
        $topic->shouldReceive('produce')
            ->withArgs([
                RD_KAFKA_PARTITION_UA,
                0,
                'a:1:{s:5:"hello";s:5:"world";}'
            ])
            ->once();

        $topicConfig = Mockery::mock(\RdKafka\TopicConf::class);

        $producer = Mockery::mock(\RdKafka\Producer::class);
        $producer->shouldReceive('newTopic')
            ->withArgs([
                'channel_name',
                $topicConfig
            ])
            ->once()
            ->andReturn($topic);

        $consumer = Mockery::mock(\RdKafka\Consumer::class);

        $adapter = new KafkaPubSubAdapter($producer, $consumer, $topicConfig);

        $adapter->publish('channel_name', ['hello' => 'world']);
    }
}
