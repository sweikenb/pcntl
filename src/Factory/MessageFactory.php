<?php

namespace Sweikenb\Library\Pcntl\Factory;

use Sweikenb\Library\Pcntl\Api\Ipc\MessageInterface;
use Sweikenb\Library\Pcntl\Model\Ipc\MessageModel;

class MessageFactory
{
    public function create(string $topic, mixed $payload): MessageInterface
    {
        return new MessageModel($topic, $payload);
    }
}
