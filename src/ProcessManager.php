<?php declare(strict_types=1, ticks=1);

namespace Sweikenb\Library\Pcntl;

use Exception;
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessFactoryInterface;
use Sweikenb\Library\Pcntl\Api\ProcessManagerInterface;
use Sweikenb\Library\Pcntl\Api\ProcessOutputInterface;
use Sweikenb\Library\Pcntl\Exception\ProcessException;
use Sweikenb\Library\Pcntl\Factory\ProcessFactory;
use Throwable;

class ProcessManager implements ProcessManagerInterface
{
    const PROPAGATE_SIGNALS = [
        SIGTERM,  // exit request
        SIGINT,   // ctrl + c
        SIGHUP,   // reload config
        SIGALRM,  // alarm
        SIGUSR1,  // custom 1
        SIGUSR2,  // custom 2
    ];
    private ProcessFactoryInterface $processFactory;
    private ProcessOutputInterface $processOutput;
    private ParentProcessInterface $mainProcess;
    /**
     * @var array<int, ChildProcessInterface>
     */
    private array $childProcesses = [];
    /**
     * @var int[]
     */
    private array $childExitQueue = [];
    private bool $isChildProcess = false;
    /**
     * @var array<int, callable>
     */
    private array $onThreadCreated = [];
    /**
     * @var array<int, callable>
     */
    private array $onThreadExit = [];

    public function __construct(
        bool $autoWait = true,
        array $propagateSignals = null,
        ?ProcessFactoryInterface $processFactory = null,
        ?ProcessOutputInterface $processOutput = null
    ) {
        // make sure we have a proper factory and output, if non is provided, use the one that comes with the library
        $this->processFactory = $processFactory ?? new ProcessFactory();
        $this->processOutput = $processOutput ?? new ProcessOutput();

        // create an instance for the current (parent) process
        $this->mainProcess = $this->processFactory->createParentProcess(posix_getpid(), null);

        // any special signals that should be handled?
        $propagateSignals = empty($propagateSignals)
            ? self::PROPAGATE_SIGNALS
            : $propagateSignals;

        // register the signal-handler for each signal that should be handled
        // we need to make sure we handle early child exists too, so add this signal no matter what
        $propagateSignals[] = SIGCHLD;
        pcntl_async_signals(false);
        foreach (array_unique($propagateSignals) as $handleSignal) {
            pcntl_signal($handleSignal, [$this, 'handleSignal']);
        }

        // prevent zombie apocalypse
        register_shutdown_function(
            function () use ($autoWait) {
                if ($autoWait) {
                    $this->wait();
                }
                if (!empty($this->childProcesses)) {
                    $this->sendSignalToChildren(
                        SIGKILL,
                        fn(ChildProcessInterface $childProcess) => $this->processOutput->stderr(
                            sprintf(
                                "[PCNTL ProcessManager] Forcing child process exit for pid %s\n",
                                $childProcess->getId()
                            )
                        )
                    );
                    $this->wait();
                    exit(1);
                }
            }
        );
    }

    public function handleSignal(int $signal): void
    {
        if ($this->isChildProcess) {
            return;
        }
        if ($signal === SIGCHLD) {
            while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) > 0) {
                $this->childExitQueue[$pid] = pcntl_wexitstatus($status);
            }
        } else {
            $this->sendSignalToChildren($signal);
        }
    }

    public function sendSignalToChildren(int $signal, ?callable $callback = null): void
    {
        foreach ($this->childProcesses as $childProcess) {
            if ($callback) {
                call_user_func($callback, $childProcess);
            }
            @posix_kill($childProcess->getId(), $signal);
        }
    }

    public function getMainProcess(): ParentProcessInterface
    {
        return $this->mainProcess;
    }

    public function runProcess(callable $callback, ?ProcessOutputInterface $output = null): ChildProcessInterface
    {
        // multi-level process-nesting is not supported (and not recommended!)
        if ($this->isChildProcess) {
            throw new ProcessException('Multi-level process-nesting not supported.');
        }

        // create IPC sockets
        $ipc = [];
        if (socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $ipc) === false) {
            throw new ProcessException(socket_strerror(socket_last_error()));
        }

        // fork now
        $pid = pcntl_fork();

        // error
        if ($pid < 0) {
            throw new ProcessException('Forking failed.');
        }

        // we are the parent
        if ($pid > 0) {
            @socket_close($ipc[0]);
            $childProcess = $this->processFactory->createChildProcess($pid, $ipc[1]);
            $this->childProcesses[$pid] = $childProcess;
            foreach ($this->onThreadCreated as $callback) {
                call_user_func($callback, $childProcess);
            }

            return $childProcess;
        }

        // we are the child
        try {
            @socket_close($ipc[1]);
            $this->childProcesses = [];
            $this->isChildProcess = true;
            $this->processOutput = $output ?? $this->processOutput;
            $success = false !== call_user_func(
                    $callback,
                    $this->processFactory->createChildProcess(posix_getpid(), null),
                    $this->getMainProcess()->setIpcSocket($ipc[0]),
                    $this->processOutput
                );
        } catch (Exception | Throwable $e) {
            $success = false;
            $this->processOutput->stderr(
                sprintf("[PCNTL ProcessManager] Child process exception: %s\n", $e->getMessage())
            );
        } finally {
            exit($success ? 0 : 1);
        }
    }

    public function wait(?callable $callback = null): void
    {
        if ($this->isChildProcess) {
            return;
        }

        $callbackStack = $this->onThreadExit;
        if ($callback) {
            array_unshift($callbackStack, $callback);
        }

        // wait for all children to exit
        while (!empty($this->childProcesses)) {
            // process the exit-queue
            foreach ($this->childExitQueue as $pid => $status) {
                if ($pid > 0) {
                    unset($this->childExitQueue[$pid], $this->childProcesses[$pid]);
                    foreach ($callbackStack as $callback) {
                        if (call_user_func($callback, $status, $pid) === false) {
                            return;
                        }
                    }
                }
            }

            // unblock the system and dispatch queued signals
            $this->unblock();
        }
    }

    public function unblock(): void
    {
        usleep(mt_rand(50, 500));
        pcntl_signal_dispatch();
    }

    public function onThreadCreate(callable $callback): self
    {
        $this->onThreadCreated[] = $callback;

        return $this;
    }

    public function onThreadExit(callable $callback): self
    {
        $this->onThreadExit[] = $callback;

        return $this;
    }
}
