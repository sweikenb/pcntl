<?php

namespace Sweikenb\Library\Pcntl;

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessManagerInterface;
use Sweikenb\Library\Pcntl\Api\ProcessOutputInterface;
use Sweikenb\Library\Pcntl\Api\ProcessQueueInterface;

class ProcessQueue implements ProcessQueueInterface
{
    private ProcessManagerInterface $processManager;
    private int $maxThreads;
    private int $threadCounter = 0;

    public function __construct(
        int $maxThreads,
        ?ProcessManagerInterface $processManager = null
    ) {
        $this->processManager = $processManager ?? new ProcessManager();
        $this->maxThreads = max(1, $maxThreads);
    }

    public function addToQueue(callable $callback, ?ProcessOutputInterface $output = null): ChildProcessInterface
    {
        while ($this->threadCounter >= $this->maxThreads) {
            $this->processManager->wait(fn() => --$this->threadCounter >= $this->maxThreads);
        }
        $this->threadCounter++;
        return $this->processManager->runProcess($callback, $output);
    }
}
