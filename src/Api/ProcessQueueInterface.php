<?php

namespace Sweikenb\Library\Pcntl\Api;

interface ProcessQueueInterface
{
    /**
     * Adds the given callback to the queue for execution.
     */
    public function addToQueue(callable $callback): ChildProcessInterface;
}
