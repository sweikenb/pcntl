<?php

namespace Sweikenb\Library\Pcntl\Api;

interface ProcessQueueInterface
{
    /**
     * Adds the given callback to the queue for execution.
     * If you specify an $output it will win over the output of the parent process.
     */
    public function addToQueue(callable $callback, ?ProcessOutputInterface $output = null): ChildProcessInterface;

    /**
     * Handles the internal thread count and dispatches the wait call to the process-manager.
     */
    public function wait(?callable $callback = null): void;

    /**
     * Returns the number of active threads. Might be zero if no tasks are scheduled.
     */
    public function getThreadCounter(): int;

    /**
     * Returns the maximum number of threads to spawn. Can not be less than one.
     */
    public function getMaxThreads(): int;

    /**
     * Returns the process-manager used for handling this queue.
     */
    public function getProcessManager(): ProcessManagerInterface;
}
