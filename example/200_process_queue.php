<?php

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\ProcessQueue;

require __DIR__ . '/../vendor/autoload.php';

$maxNumberOfThreadsToRunParallel = 4;

$queue = new ProcessQueue($maxNumberOfThreadsToRunParallel);

for ($i = 1; $i <= 50; $i++) {
    $queue->addToQueue(function (ChildProcessInterface $child, ParentProcessInterface $parent) use ($i) {
        sleep(mt_rand(1, 3));
        fwrite(STDOUT, sprintf("Queued thread %d processed message no. %d\n", $child->getId(), $i));
    });
}
