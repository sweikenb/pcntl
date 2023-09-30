# Process Queue

If you do not know how many threads you might need, but you want to limit the amount of threads that will be forked
simultaneously, you can use the `ProcessQueue` which internally ensures that your thread-limit is never exceeded.

## Basic Usage

```php
use Sweikenb\Library\Pcntl\ProcessQueue;

require __DIR__ . '/vendor/autoload.php';

// only 4 threads will be forked and further callbacks must wait
// until free slots are available
$maxThreads = 4;

$queue = new ProcessQueue($maxThreads);
for ($i = 0; $i < 100; $i++) {
    $queue->addToQueue(fn() => sleep(3));
}
```

## Settings

- `$maxThreads`
    - the maximum number of threads that might be forked _(min. `1`)_
- `$processManager`
    - instance of the process manager to be used
    - default: `Sweikenb\Library\Pcntl\ProcessManager`
