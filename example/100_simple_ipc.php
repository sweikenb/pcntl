<?php

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Factory\MessageFactory;
use Sweikenb\Library\Pcntl\ProcessManager;

require __DIR__ . '/../vendor/autoload.php';

$numWorker = 4;
$numMessages = 25;

$pm = new ProcessManager();
$factory = new MessageFactory();

$workers = [];
/* @var array<int, ChildProcessInterface> $workers */

for ($i = 0; $i < $numWorker; $i++) {
    $workers[$i] = $pm->runProcess(
        function (ChildProcessInterface $process, ParentProcessInterface $parentProcess) use ($i, $factory) {
            fwrite(
                STDOUT,
                sprintf("> Worker #%d: started and ready to process messages\n", ($i + 1))
            );
            $count = 0;
            while ($message = $parentProcess->getNextMessage()) {
                $count++;
                fwrite(
                    STDOUT,
                    sprintf(
                        ">> Worker #%d: received a message: '%s' '%s' (no. msg.: %d)\n",
                        ($i + 1),
                        $message->getTopic(),
                        $message->getPayload(),
                        $count
                    )
                );
                $parentProcess->sendMessage(
                    $factory->create(
                        sprintf('Answer from #%d', $process->getId()),
                        sprintf("msg %d", $count)
                    )
                );
            }
        }
    );
}

for ($i = 0; $i < $numWorker * $numMessages; $i++) {
    $workerId = $i % $numWorker;
    $message = $factory->create('some message for ' . ($workerId + 1), 'some payload for ' . ($workerId + 1));
    $workers[$workerId]->sendMessage($message);
}

foreach ($workers as $i => $worker) {
    $count = 0;
    while ($count < $numMessages) {
        $count++;
        $message = $worker->getNextMessage();
        fwrite(
            STDOUT,
            sprintf(
                ">> Worker #%d answered with message: '%s' '%s'\n",
                $worker->getId(),
                $message->getTopic(),
                $message->getPayload()
            )
        );
    }

    // stop the worker process
    fwrite(STDOUT, sprintf('# Stopping worker (%d)', $worker->getId()));
    $worker->kill();
}
