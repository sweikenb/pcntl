<?php

namespace Sweikenb\Library\Pcntl\Api;

use Sweikenb\Library\Pcntl\Api\Ipc\WorkerMessageInterface;

interface ProcessPoolInterface
{
    public function sendMessage(WorkerMessageInterface $workerMessage): bool;

    public function killAll(): void;
}
