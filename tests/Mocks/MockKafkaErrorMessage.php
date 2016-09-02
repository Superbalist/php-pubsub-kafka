<?php

namespace Tests\Mocks;

class MockKafkaErrorMessage
{
    public $err = 1234;

    public $payload = null;

    public function errstr()
    {
        return 'This is an error message.';
    }
}
