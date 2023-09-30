<?php

namespace Sweikenb\Library\Pcntl\Api;

interface ProcessQueueInterface
{
    /**
     * Adds the given callback to the queue for execution.
     * If you specify an $output it will win over the output of the parent process.
     */
    public function addToQueue(callable $callback, ?ProcessOutputInterface $output = null): ChildProcessInterface;
}
