<?php

use Sweikenb\Library\Pcntl\Factory\WorkerMessageFactory;
use Sweikenb\Library\Pcntl\ProcessPool;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/ExampleWorkerService.php';

$numWorker = 4;
$numMessages = 25 * $numWorker;
$factory = new WorkerMessageFactory();

$pool = new ProcessPool($numWorker);
for ($i = 0; $i < $numMessages; $i++) {
    $pool->sendMessage(
        $factory->create('hello_world', ExampleWorkerService::class)
    );
}

// Give the workers some time to work.
// Usually you would send messages in some kid of event/endless-loop and/or with some custom unblock logic.
sleep(3);

// Work done, kill all workers and disable the pool. No more messages can be processed.
// This will be called automatically if the main process terminated.
$pool->closePool();
