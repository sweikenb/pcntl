# Common Pitfalls and Workarounds

## Database Connections and File-Pointer

When working with database-connections, file-pointer or any other kind of ressource, you have to be careful when forking
processes.

A fork is a copy of the original thread that contains a copy of the memory too. The pointer and resources referenced by
the copied memory still belong to the main thread and when accessing them it wil cause errors and a lot of problems.

Fortunately, there are some simple workarounds:

1. Do not open any connection or pointer before you fork your process _(easy enough)_
2. If you need to open a connection or resource, close it and create a new instance inside the fork:

```php
use Sweikenb\Library\Pcntl\ProcessManager;

require __DIR__ . '/vendor/autoload.php';

$pm = new ProcessManager();

// get the connection
$connection = new Connection();

// load results
$results = $connection->getReults();

// close connection
$connection->close();
foreach ($results as $result) {
    $pm->runProcess(function () use ($result) {
        // close connection
        $connection = new Connection();

        // TODO process data

        // update data
        $connection->update($result);

        // close connection
        $connection->close();
    });
}
```
