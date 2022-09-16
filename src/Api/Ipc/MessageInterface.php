<?php

namespace Sweikenb\Library\Pcntl\Api\Ipc;

use Serializable;

interface MessageInterface extends Serializable
{
    /**
     * Must return a topic that might be used for later message routing.
     *
     * @return string
     */
    public function getTopic(): string;

    /**
     * Payload of the message that can be anything that is "serializable()".
     *
     * @return mixed
     */
    public function getPayload(): mixed;
}
