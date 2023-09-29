<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sweikenb\Library\Pcntl\ProcessManager;
use Sweikenb\Library\Pcntl\ProcessQueue;

class ProcessQueueTest extends TestCase
{
    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessQueue::addToQueue
     */
    public function testAddToQueue(): void
    {
        $active = 0;
        $maxThreads = 4;

        $pm = new ProcessManager();
        $pm->onThreadExit(function () use ($maxThreads, &$active) {
            $active--;
            $this->assertLessThanOrEqual($maxThreads, $active);
        });

        $queue = new ProcessQueue($maxThreads, $pm);
        for ($i = 0; $i < 20; $i++) {
            $active++;
            $queue->addToQueue(fn() => 'test');
        }
        $pm->wait(function () use ($maxThreads, &$active) {
            $this->assertLessThanOrEqual($maxThreads, $active);
        });
    }
}
