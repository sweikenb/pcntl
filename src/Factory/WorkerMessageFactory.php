<?php

namespace Sweikenb\Library\Pcntl\Factory;

use Sweikenb\Library\Pcntl\Api\Ipc\WorkerMessageInterface;
use Sweikenb\Library\Pcntl\Model\Ipc\WorkerMessageModel;

class WorkerMessageFactory
{
    public function create(string $topic, string $workerClass): WorkerMessageInterface
    {
        return new WorkerMessageModel($topic, $workerClass);
    }
}
