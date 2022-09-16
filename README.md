# PCNTL Library

Simple and easy to use process manager for PHP based on default PCNTL and POSIX functions.

## Installation

You can install this library by [composer](https://getcomposer.org/):

```bash
composer require sweikenb/pcntl
```

## Usage

You can just create an instance of `\Sweikenb\Library\Pcntl\ProcessManager` and create a process-fork by
calling `runProcess()`.

The manager will handle the rest and makes sure all process will be terminated properly. It will also make sure that
the major system signals will be propagated to the child processes as well. In case you want to define your own set of
signals you want to propagate to the children, you can add an array with the signals as second argument to the
constructor.

**Process flow**

You can _(but you do not have to)_ use the `wait()` method to wait for previously created children. The method can be
called multiple times and allows a very flexible process-flow handling.

Also, to make sure the children are terminated, the process-manager will install a shutdown-function which will call
the `wait()` method automatically at the end of the script. If you do not want this functionality, just pass `false` as
first argument to the constructor to disable the shutdown handler. If you disable this feature, the process manager will
force a child termination even if they aren't finished yet and exits with status-code `125`.

## Event Dispatcher Support

This library supports the event dispatcher component of Symfony. You can inject the event-listener to
the `ProcessManager` to dispatch events during program runtime.

**Please note that events will only be dispatched for the parent/main process.**

```php
$eventManager = new EventDispatcher();
$eventManager->addListener(ProcessManager::EVENT_CHILD_CREATED, function (ProcessManagerEvent $event) {
    echo sprintf("Child created: %d\n", $event->getProcessId());
});

$pm = new ProcessManager();
$pm->setEventDispatcher($eventManager);

// ...
```

The following events are thrown:

| Event                           | Description                                |
|---------------------------------|--------------------------------------------|
| process.manager.fork.failed     | Forking of the process failed.             |
| process.manager.child.created   | Fork was created successfully.             |
| process.manager.child.exit      | A child has exited.                        |
| process.manager.child.send.kill | A kill signal was sent to a child process. |

## Inter Process Communication

You can send data between the parent and child process using messages.

The data gets send by sockets and can be anything that can be encoded using `serialize()`:

```php
<?php

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\ProcessManager;
use Sweikenb\Library\Pcntl\Model\Ipc\MessageModel;

require "./vendor/autoload.php";

$pm = new ProcessManager();

$child = $pm->runProcess(function(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess){
    $message = $parentProcess->getNextMessage(true);
    if ($message) {
        // Process message here ...
        fwrite(
            STDOUT,
            fprintf('Got a message from the parent process: %s - %s', $message->getTopic(), $message->getPayload())
        );
    }
    $parentProcess->sendMessage(new MessageModel('some_response', 'hello parent'));
});

$child->sendMessage(new MessageModel('some_topic', 'hello child'));

// wait and cleanup
sleep(3);
$child->kill();
```

## Process Pool & Worker Processes

You can also distribute workload across multiple worker to work in parallel. The actual work must be placed inside a
class that is invokable _(`__invoke`)_ and must not have a constructor.

```php
<?php

use ExampleWorkerService;
use Sweikenb\Library\Pcntl\Factory\WorkerMessageFactory;
use Sweikenb\Library\Pcntl\ProcessPool;

require "./vendor/autoload.php";
$messageFactory = new WorkerMessageFactory();

$numberOfWorkers = 4;
$pool = new ProcessPool($numberOfWorkers);

for($i = 0; $i < 100; $i++) {
    $pool->sendMessage($messageFactory->create('some_topic', ExampleWorkerService::class));
}

// wait and cleanup
sleep(5);
$pool->killAll();
```

## Example

```php
<?php

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\ProcessManager;

require "./vendor/autoload.php";

$pm = new ProcessManager();

$pm->runProcess(function(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess){
    sleep(2);
    echo "B\n";
});

$pm->runProcess(function(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess){
    sleep(1);
    echo "C\n";
});

$pm->runProcess(function(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess){
    sleep(3);
    echo "A\n";
});

$pm->wait(function(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess){
    echo "-> A to C processes finished!\n";
});

$pm->runProcess(function(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess){
    echo "E\n";
});

$pm->runProcess(function(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess){
    sleep(1);
    echo "D\n";
});

$pm->wait(function(){
    echo "-> D and E processes finished!\n";
});

echo "\n--> All Work done!\n";
```

This script will return the following output:

```bash
C
B
A
-> A to C processes finished!
E
D
-> D and E processes finished!

--> All Work done!
```

More examples including the `EventDispatcher` can be found in the [example](./example) folder.
