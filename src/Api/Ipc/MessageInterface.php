<?php

namespace Sweikenb\Library\Pcntl\Api\Ipc;

use Serializable;

interface MessageInterface extends Serializable
{
    public function getTopic(): string;

    public function getPayload(): mixed;
}
