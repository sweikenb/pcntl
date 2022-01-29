<?php declare(strict_types=1);

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Event\ProcessManagerEvent;
use Sweikenb\Library\Pcntl\ProcessManager;
use Symfony\Component\EventDispatcher\EventDispatcher;

require __DIR__ . '/../vendor/autoload.php';

$pm = new ProcessManager();
if (class_exists(EventDispatcher::class)) {
    /*
     * Register events in case the Symfony EventDispatcher is available
     */
    $eventManager = new EventDispatcher();
    $eventManager->addListener(ProcessManager::EVENT_CHILD_CREATED, function (ProcessManagerEvent $event) {
        echo sprintf("[EVENT CALLBACK] Child created: %d\n", $event->getProcessId());
    });
    $pm->setEventDispatcher($eventManager);
}

$childA = $pm->runProcess(
    function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) {
        sleep(mt_rand(1, 10));
        echo "Hallo from child A\n";
    }
);
$childB = $pm->runProcess(
    function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) {
        sleep(mt_rand(1, 10));
        echo "Hallo from child B\n";
    }
);
$childC = $pm->runProcess(
    function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) {
        sleep(mt_rand(1, 10));
        echo "Hallo from child C\n";
    }
);
$childD = $pm->runProcess(
    function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) {
        sleep(mt_rand(1, 10));
        echo "Hallo from child D\n";
    }
);

echo "Main Process knows the following children:\n";
echo sprintf(">> A: %s\n", $childA->getId());
echo sprintf(">> B: %s\n", $childB->getId());
echo sprintf(">> C: %s\n", $childC->getId());
echo sprintf(">> D: %s\n", $childD->getId());

// HINT
// just  $pm->wait();  without any callback would work as well
$pm->wait(
    function (int $status, int $pid) {
        echo sprintf("The child with PID %s exited with status %s\n", $pid, $status);
    }
);

// HINT
// If you enable the $autoWait option of the default ProcessManager (enabled by default), you could also skipp the
// whole "wait"-mechanism if you do not need this for your script.
