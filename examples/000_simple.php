<?php
declare(strict_types=1);

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\ProcessManager;

require __DIR__ . '/../vendor/autoload.php';

$pm = new ProcessManager();

$childA = $pm->runProcess(
    function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) {
        sleep(5);
        echo "Hallo from child A\n";
    }
);
$childB = $pm->runProcess(
    function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) {
        sleep(3);
        echo "Hallo from child B\n";
    }
);
$childC = $pm->runProcess(
    function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) {
        sleep(6);
        echo "Hallo from child C\n";
    }
);
$childD = $pm->runProcess(
    function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) {
        sleep(4);
        echo "Hallo from child D\n";
    }
);

echo "Main Process knows the following childs:\n";
echo sprintf(">> A: %s\n", $childA->getId());
echo sprintf(">> B: %s\n", $childB->getId());
echo sprintf(">> C: %s\n", $childC->getId());
echo sprintf(">> D: %s\n", $childD->getId());


// HINT
// just  $pm->wait();  without any callback would work aswell
$pm->wait(
    function (int $status, int $pid) {
        echo sprintf("The child with PID %s exited with status %s\n", $pid, $status);
    }
);

// HINT
// If you enable the $autoWait option of the default ProcessManager (enabled by default), you could also skipp the
// whole "wait"-mechanism if you do not need this for your script.
