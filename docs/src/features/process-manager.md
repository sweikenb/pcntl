# Process Manager

The process manager is the core of this library and provides the basic threading functionality by utilizing the native
functions of the [pcntl](https://www.php.net/pcntl) and [posix](https://www.php.net/posix) modules.

## Basic Usage

```php
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface as ChildProcess;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface as ParentProcess;
use Sweikenb\Library\Pcntl\Api\ProcessOutputInterface as Output;
use Sweikenb\Library\Pcntl\ProcessManager;

require __DIR__ . '/vendor/autoload.php';

$pm = new ProcessManager();

// inline callback
$pm->runProcess(fn() => sleep(3));

// example with all available callback parameters
$pm->runProcess(function (ChildProcess $child, ParentProcess $parent, Output $output) {
    $output->stdout(sprintf("Parent PID: %s\n", $parent->getId()));
    $output->stdout(sprintf("Child PID: %s\n", $child->getId()));
});

// return 'true' or 'null'/'void' to indicate that the execution was successful
$pm->runProcess(function () {
    //...
    return true; // this will exit the child process with exit-code `0`
});

// return 'false' to indicate that the execution failed
$pm->runProcess(function () {
    //...
    return false; // this will exit the child process with exit-code `1`
});

// prints the PID of the main process
var_dump($pm->getMainProcess()->getId());
```

### Wait for Children

If you want to continue synchronously after creating child-threads, simply call the `wait()`-method of the process
manager. By default, the method is called automatically at the end of each script-execution but this can be configured
using the `$autoWait`-[setting](#settings) of the `ProcessManager`.

```php
use Sweikenb\Library\Pcntl\ProcessManager;

require __DIR__ . '/vendor/autoload.php';

$pm = new ProcessManager();

// run in sync
// ...

// run the next three lines async
$pm->runProcess(fn() => sleep(3));
$pm->runProcess(fn() => sleep(1));
$pm->runProcess(fn() => sleep(2));

// wait for all threads to finish
$pm->wait();

// continue to run in sync from here
// ...
```

If you wat to know which children exited, you can provide a callback function for the `wait()`-method:

```php
// wait for all threads to finish
$pm->wait(function (int $status, int $pid) {
    echo sprintf("The child with pid %s exited with status code %s\n", $pid, $status);
});
```

By default, the `wait()`-method will wait for **ALL** children to exit before it continues the programm. If you wish to
only wait for a specific children to exit, you can modify this behavior by returning `false` in the callback:

```php
$pm->wait(function (int $status, int $pid) {
    if ($status === 1) {
        // the child failed, lets stop waiting and continue with the programm-flow
        return false;
    }

    // info
    echo sprintf("The child with pid %s exited with status code %s\n", $pid, $status);

    // continue to wait
    return true;
});
```

Beside the callback in the wait()-method itself, there are also [thread-hooks](#thread-hooks) that can be used to get
notified when a
thread is created or finished.

### Thread-Hooks

You can register hooks that gets triggered during the lifetime of a thread. Note that you can register multiple hooks
for the same lifetime-event and that the callbacks are executed in the order of registration.

#### onThreadCreate

Registers a callback that gets called whenever a thread is created:

```php
$pm->onThreadCreate(function (ChildProcessInterface $child) {
    echo sprintf("The child with pid %s was created.", $child->getId());
});
```

#### onThreadExit

Registers a callback that gets called whenever a thread exits:

```php
$pm->onThreadExit(function (int $status, int $pid) {
    echo sprintf("The child with pid %s exited with status %s", $pid, $status);
});
```

Please note that the callback of the `wait()` method gets called BEFORE the lifecycle hooks.

## Settings

- `$autoWait`
    - enables or disables the automatic wait at the end of the script
    - default: `true` _RECOMMENDED!_
- `$propagateSignals`
    - list of signals that should be propagated to the child-processes
    - default signals:
        - `SIGTERM` graceful exit request by the system or user
        - `SIGINT` user interrupts the execution (e.g. `ctrl` + `c` in the terminal)
        - `SIGHUP` usually used to request a config reload
        - `SIGALRM` usually used for timeout management
        - `SIGUSR1` custom signal 1
        - `SIGUSR2` custom signal 2
    - please note that `SIGCHLD` can NOT be propagated due to how the process-manager internally handles this signal
- `$processFactory`
    - factory instance that should be used to create the process models
    - default: `Sweikenb\Library\Pcntl\Factory\ProcessFactory`
- `$processOutput`
    - output instance that should be used as proxy for writing data to `STDOUT` and `STDERR`
    - default: `Sweikenb\Library\Pcntl\ProcessOutput`
