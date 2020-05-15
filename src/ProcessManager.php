<?php
declare(strict_types=1);

namespace Sweikenb\Library\Pcntl;

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessFactoryInterface;
use Sweikenb\Library\Pcntl\Api\ProcessManagerInterface;
use Sweikenb\Library\Pcntl\Exception\ProcessException;
use Sweikenb\Library\Pcntl\Factory\ProcessFactory;

class ProcessManager implements ProcessManagerInterface
{
    /**
     * @var int[]
     */
    private const PROPAGATE_SIGNALS = [
        SIGTERM,
        SIGHUP,
        SIGUSR1,
        SIGALRM,
    ];
    /**
     * @var \Sweikenb\Library\Pcntl\Api\ProcessFactoryInterface
     */
    private $processFactory;
    /**
     * @var \Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
     */
    private $mainProcess;
    /**
     * @var \Sweikenb\Library\Pcntl\Api\ProcessInterface[]
     */
    private $childProcesses = [];
    /**
     * @var bool
     */
    private $isChildProcess = false;

    /**
     * ProcessManager constructor.
     *
     * @param bool $autoWait
     * @param array|null $propagateSignals
     * @param \Sweikenb\Library\Pcntl\Api\ProcessFactoryInterface|null $processFactory
     */
    public function __construct(
        bool $autoWait = true,
        array $propagateSignals = null,
        ?ProcessFactoryInterface $processFactory = null
    ) {
        // make sure we have a proper factory, if non is provided, use the one that comes with the library
        if (!$processFactory) {
            $processFactory = new ProcessFactory();
        }
        $this->processFactory = $processFactory;

        // create an instance for the current (parent) process
        $this->mainProcess = $this->processFactory->createParentProcess(posix_getpid());

        // any special signals that should be handled?
        $propagateSignals = empty($propagateSignals)
            ? self::PROPAGATE_SIGNALS
            : $propagateSignals;

        // register the signal-handler for each signal that should be handled
        pcntl_async_signals(true);
        foreach ($propagateSignals as $handleSignal) {
            pcntl_signal(
                $handleSignal,
                function (int $dispatchSignal) {
                    foreach ($this->childProcesses as $childProcess) {
                        @posix_kill($childProcess->getId(), $dispatchSignal);
                    }
                }
            );
        }

        // prevent zombie apocalypse
        register_shutdown_function(
            function () use ($autoWait) {
                if ($autoWait) {
                    $this->wait();
                } else {
                    if (!empty($this->childProcesses)) {
                        foreach ($this->childProcesses as $childProcess) {
                            echo sprintf(
                                "[PCNTL ProcessManager] Forcing child process exit for pid %s\n",
                                $childProcess->getId()
                            );
                            @posix_kill($childProcess->getId(), SIGKILL);
                        }
                        $this->wait();

                        // In case we had to force a child kill, exit with the exit code 125 (operation canceled)
                        exit(125);
                    }
                }
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function getMainProcess(): ParentProcessInterface
    {
        return $this->mainProcess;
    }

    /**
     * @inheritDoc
     */
    public function runProcess(callable $callback): ChildProcessInterface
    {
        // multi-level process-nesting is not supported (and not recommended!)
        if ($this->isChildProcess) {
            throw new ProcessException('Multi-level process-nesting not supported.');
        }

        // fork now
        $pid = pcntl_fork();

        // error
        if ($pid < 0) {
            throw new ProcessException('Forking failed.');
        }

        // we are the parent
        if ($pid > 0) {
            $childProcess = $this->processFactory->createChildProcess($pid);
            $this->childProcesses[$pid] = $childProcess;

            return $childProcess;
        }

        // we are the child
        try {
            $this->childProcesses = [];
            $this->isChildProcess = true;
            call_user_func(
                $callback,
                $this->processFactory->createChildProcess(posix_getpid()),
                $this->getMainProcess()
            );
        } finally {
            exit(0);
        }
    }

    /**
     * @inheritDoc
     */
    public function wait(callable $callback = null): ProcessManagerInterface
    {
        if (!$this->isChildProcess) {
            while (!empty($this->childProcesses)) {
                $pid = pcntl_wait($status);
                if (isset($this->childProcesses[$pid])) {
                    unset($this->childProcesses[$pid]);
                    if (null !== $callback) {
                        call_user_func($callback, $status, $pid);
                    }
                }
            }
        }

        return $this;
    }
}