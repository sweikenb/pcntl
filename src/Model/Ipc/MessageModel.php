<?php

namespace Sweikenb\Library\Pcntl\Model\Ipc;

use Sweikenb\Library\Pcntl\Api\Ipc\MessageInterface;

class MessageModel implements MessageInterface
{
    public function __construct(
        private string $topic,
        private mixed $payload
    ) {
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }

    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    public function unserialize(string $data): void
    {
        $this->__unserialize(unserialize($data));
    }

    public function __serialize(): array
    {
        return [
            'topic' => $this->topic,
            'payload' => $this->payload,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->topic = $data['topic'] ?? '';
        $this->payload = $data['payload'] ?? '';
    }
}
