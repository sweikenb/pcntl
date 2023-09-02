<?php

namespace Sweikenb\Library\Pcntl\Api;

use Sweikenb\Library\Pcntl\Api\Ipc\WorkerMessageInterface;

interface ProcessPoolInterface
{
    public function getInvocationBuilder(): callable;

    public function sendMessage(WorkerMessageInterface $workerMessage): bool;

    public function closePool(): void;
}
