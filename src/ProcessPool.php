<?php

namespace Sweikenb\Library\Pcntl;

use Exception;
use ReflectionClass;
use ReflectionException;
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\Ipc\WorkerMessageInterface;
use Sweikenb\Library\Pcntl\Api\Ipc\WorkerSelectionStrategyInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessManagerInterface;
use Sweikenb\Library\Pcntl\Api\ProcessPoolInterface;
use Sweikenb\Library\Pcntl\Exception\ProcessException;
use Sweikenb\Library\Pcntl\Strategies\RoundRobinWorkerSelectionStrategy;
use Throwable;

class ProcessPool implements ProcessPoolInterface
{
    protected bool $isClosed = false;
    protected int $numWorkers;
    protected $invocationBuilder;
    private WorkerSelectionStrategyInterface $workerSelectionStrategy;
    private ProcessManager $processManager;
    /**
     * @var array<int, ChildProcessInterface>
     */
    protected array $pool = [];

    public function __construct(
        int $numWorkers,
        ?callable $invocationBuilder = null,
        ?WorkerSelectionStrategyInterface $workerSelectionStrategy = null,
        ?ProcessManagerInterface $processManager = null
    ) {
        // normalize arguments and ensure we have proper instances (or create a default fallback)
        $this->numWorkers = max(1, $numWorkers);
        $this->invocationBuilder = $invocationBuilder ?? function (...$args): mixed {
            return $this->defaultInvoker(...$args);
        };
        $this->workerSelectionStrategy = $workerSelectionStrategy ?? new RoundRobinWorkerSelectionStrategy();
        $this->processManager = $processManager ?? new ProcessManager();

        // prevent zombie apocalypse
        register_shutdown_function([$this, 'closePool']);

        // start the worker processes
        for ($workerNo = 0; $workerNo < $this->numWorkers; $workerNo++) {
            $this->startWorker();
        }
    }

    private function startWorker(): ChildProcessInterface
    {
        // closed?
        if ($this->isClosed) {
            throw new ProcessException('Can not start new worker as the process pool is closed.');
        }

        // create process
        $process = $this->processManager->runProcess(function (...$args) {
            $this->handleMessage(...$args);
        });

        // register process
        $this->pool[$process->getId()] = $process;

        // reconfigure selection-strategy
        $this->workerSelectionStrategy->configure(array_keys($this->pool));

        // return created process
        return $process;
    }

    private function handleMessage(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess): void
    {
        try {
            while ($message = $parentProcess->getNextMessage()) {
                if ($message instanceof WorkerMessageInterface) {
                    $message->execute($this, $childProcess, $parentProcess);
                }
            }
        } catch (Exception | Throwable $e) {
            fwrite(
                STDERR,
                sprintf(
                    "Worker %d encountered an error: %s (%s:%d)\n",
                    $childProcess->getId(),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }
    }

    /**
     * @throws ProcessException
     * @throws ReflectionException
     */
    private function defaultInvoker(
        string $class,
        ChildProcessInterface $childProcess,
        ParentProcessInterface $parentProcess
    ): mixed {
        // class available?
        if (!class_exists($class)) {
            throw new ProcessException('Workload class not found!');
        }

        // check if the worker requires non-optional constructor params
        $ref = new ReflectionClass($class);
        $constructor = $ref->getConstructor();
        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $parameter) {
                if (!$parameter->isDefaultValueAvailable()) {
                    throw new ProcessException(
                        'The provided worker requires constructor arguments but ' .
                        'no custom invocation callback is configured.'
                    );
                }
            }
        }

        // get the worker instance
        $worker = $ref->newInstance();
        if (!is_callable($worker)) {
            throw new ProcessException('Provided class is not invokable!');
        }

        // invoke the worker now
        return $worker($childProcess, $parentProcess);
    }

    private function getNextWorker(): ChildProcessInterface
    {
        // if process pool is closed, we can not get a worker process
        if ($this->isClosed) {
            throw new ProcessException('Process pool is closed!');
        }

        // get next pid to distribute load to
        $pid = $this->workerSelectionStrategy->getNextWorkerPid();

        // initially known process?
        $worker = $this->pool[$pid] ?? null;
        /* @var ChildProcessInterface|null $worker */

        // ensure the process is still running
        if ($worker === null || !file_exists(sprintf("/proc/%d", $pid))) {
            if (isset($this->pool[$pid])) {
                unset($this->pool[$pid]);
            }

            // start a drop-in worker
            $worker = $this->startWorker();
        }

        return $worker;
    }

    public function getInvocationBuilder(): callable
    {
        return $this->invocationBuilder;
    }

    public function sendMessage(WorkerMessageInterface $workerMessage): bool
    {
        return $this->getNextWorker()->sendMessage($workerMessage);
    }

    public function closePool(): void
    {
        // already closed?
        if ($this->isClosed) {
            return;
        }

        // close now
        $this->isClosed = true;
        foreach ($this->pool as $pid => $worker) {
            $worker->kill();
            unset($this->pool[$pid], $worker);
        }
        $this->processManager->wait();
    }
}
