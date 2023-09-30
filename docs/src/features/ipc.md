# Inter Process Communication (IPC)

When working with threads, you might want to send data between the parent- and the child-process _(e.g. to update data
or return the result of an asynchronous workload)_.

In order to do so, the threads have a direct socket-connection which can be used to send messages with custom payloads.

## Topic and Payload

The **topic** is intended to be used as description, intention or routing of the message. Beside the fact that it must
be a string, the topic can be anything you like.

The **payload** on the other hand is intended to carry the actual data you want to transfer between the threads.
Please beware that you can only send payloads that are [serializable](https://www.php.net/serialize). Any kind of
file-pointer or resource _(e.g.
[database-connections](../common-pitfalls-and-workarounds.md#database-connections-and-file-pointer))_ will **NOT** work!

You might also want to refer to the [Common Pitfalls and Workarounds](../common-pitfalls-and-workarounds.md) section if
you run into trouble.

## Basic Usage

```php
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface as ChildProcess;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface as ParentProcess;
use Sweikenb\Library\Pcntl\Factory\MessageFactory;
use Sweikenb\Library\Pcntl\ProcessManager;

require __DIR__ . '/vendor/autoload.php';

$pm = new ProcessManager();
$factory = new MessageFactory();

$child = $pm->runProcess(
    function (ChildProcess $child, ParentProcess $parent) use ($factory) {
        $messageFromParent = $parent->getNextMessage();
        $parent->sendMessage(
            $factory->create(
                'hello parent',
                [
                    'pid' => $child->getId(),
                    'lastMessage' => $messageFromParent->getTopic()
                ]
            )
        );
    }
);

$child->sendMessage($factory->create('hello child', null));
$messageFromChild = $child->getNextMessage();

var_dump($messageFromChild);
```

This will output something like this:

```
class Sweikenb\Library\Pcntl\Model\Ipc\MessageModel#14 (2) {
  private string $topic =>
  string(12) "hello parent"
  private mixed $payload =>
  array(2) {
    'pid' =>
    int(54723)
    'lastMessage' =>
    string(11) "hello child"
  }
}

Process finished with exit code 0
```
