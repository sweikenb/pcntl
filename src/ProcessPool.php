<?php

namespace Sweikenb\Library\Pcntl;

use Exception;
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\Ipc\WorkerMessageInterface;
use Sweikenb\Library\Pcntl\Api\Ipc\WorkerSelectionStrategyInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessManagerInterface;
use Sweikenb\Library\Pcntl\Api\ProcessPoolInterface;
use Sweikenb\Library\Pcntl\Strategies\RoundRobinWorkerSelectionStrategy;

class ProcessPool implements ProcessPoolInterface
{
    protected int $numWorkers;
    private WorkerSelectionStrategyInterface $workerSelectionStrategy;

    /**
     * @var array<int, ChildProcessInterface>
     */
    protected array $pool = [];

    public function __construct(
        int $numWorkers,
        ?WorkerSelectionStrategyInterface $workerSelectionStrategy = null,
        ?ProcessManagerInterface $processManager = null
    ) {
        // normalize arguments and ensure we have proper instances (or create a default fallback)
        $this->numWorkers = max(1, $numWorkers);
        $this->workerSelectionStrategy = $workerSelectionStrategy ?? new RoundRobinWorkerSelectionStrategy();
        $processManager = $processManager ?? new ProcessManager();

        // create worker processes
        for ($workerNo = 0; $workerNo < $this->numWorkers; $workerNo++) {
            $process = $processManager->runProcess(
                function (ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess) use ($workerNo) {
                    try {
                        $workerNo += 1;
                        fwrite(
                            STDOUT,
                            sprintf(
                                "Worker (%d/%d) ready to process worker-messages...\n",
                                $workerNo,
                                $this->numWorkers
                            )
                        );
                        while ($message = $parentProcess->getNextMessage()) {
                            if ($message instanceof WorkerMessageInterface) {
                                $message->execute($childProcess, $parentProcess);
                            }
                        }
                    } catch (Exception $e) {
                        fwrite(
                            STDERR,
                            sprintf(
                                "Worker #%d (pid %d) encountered an error: %s (%s:%d)\n",
                                $workerNo,
                                $childProcess->getId(),
                                $e->getMessage(),
                                $e->getFile(),
                                $e->getLine()
                            )
                        );
                    }
                }
            );
            $this->pool[$process->getId()] = $process;
        }

        // configure the worker selection strategy now
        $this->workerSelectionStrategy->configure(array_keys($this->pool));
    }

    public function sendMessage(WorkerMessageInterface $workerMessage): bool
    {
        /* @var ChildProcessInterface|null $worker */
        $worker = $this->pool[$this->workerSelectionStrategy->getNextWorkerPid()] ?? null;
        if ($worker === null) {
            return false;
        }
        return $worker->sendMessage($workerMessage);
    }

    public function killAll(): void
    {
        foreach ($this->pool as $i => $worker) {
            $worker->kill();
            unset($this->pool[$i], $worker);
        }
    }
}
