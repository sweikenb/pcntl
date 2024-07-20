<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sweikenb\Library\Pcntl\ProcessManager;
use Sweikenb\Library\Pcntl\ProcessQueue;

class ProcessQueueTest extends TestCase
{
    const TEST_MAX_THREADS = 4;

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessQueue::addToQueue
     */
    public function testAddToQueue(): void
    {
        $pm = new ProcessManager();
        $queue = new ProcessQueue(self::TEST_MAX_THREADS, $pm);

        $pm->onThreadCreate(function () use ($queue) {
            $this->assertLessThanOrEqual(self::TEST_MAX_THREADS, $queue->getThreadCounter());
        });
        $pm->onThreadExit(function () use ($queue) {
            $this->assertLessThanOrEqual(self::TEST_MAX_THREADS, $queue->getThreadCounter());
        });

        for ($i = 0; $i < 20; $i++) {
            $queue->addToQueue(fn() => 'test');
        }

        $queue->wait(function () use ($queue) {
            $this->assertLessThanOrEqual(self::TEST_MAX_THREADS, $queue->getThreadCounter());
        });

        $this->assertSame(self::TEST_MAX_THREADS, $queue->getMaxThreads());
        $this->assertSame(0, $queue->getThreadCounter());
    }
}
