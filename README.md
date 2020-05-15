# PCNTL Library

Simple and easy to use process manager for PHP based on default PCNTL and POSIX functions.


## Installation

You can install this library by [composer](https://getcomposer.org/):

```bash
composer require "sweikenb/pcntl":"^1.0"
```


## Usage

You can just create an instance of `\Sweikenb\Library\Pcntl\ProcessManager` and create a process-fork by calling `runProcess()`.

The manager will handle the rest and makes shure all process will be terminated properly. It will also make shure that the major system signals will be propagated to the child processes aswell. In case you want to define your own set of signlas you want to propagate to the childs, you cann add an array with the signales as second argument to the constructor.

**Process flow**

You can _(but you do not have to)_ use the `wait()` method to wait for previously created childs. The method can be calles multiple times and allows a very flexible process-flow handling.

Also, to make shure the childs are terminated, the process-manager will install a shutdown-function wich will call the `wait()` method automatically at the end of the script. If you do not want this functionality, just pass `false` as first argument to the constructor to disable the shutdown handler. If you disable this feature, the process manager will force a child termination even if they aren't finished yet and exits with status-code `125`.


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

More examples can be found in the [examples](./examples) folder.
