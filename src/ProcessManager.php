<?php declare(strict_types=1);

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
        SIGTERM,
        SIGHUP,
        SIGALRM,
        SIGUSR1,
        SIGUSR2
    ];
    private ProcessFactoryInterface $processFactory;
    private ProcessOutputInterface $processOutput;
    private ParentProcessInterface $mainProcess;
    /**
     * @var array<int, ChildProcessInterface>
     */
    private array $childProcesses = [];
    /**
     * @var array<int, int>
     */
    private array $earlyExitChildQueue = [];
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

        // register a signale queue for early exit children
        pcntl_async_signals(false);
        pcntl_signal(SIGCHLD, [$this, "childEarlyExitQueue"]);

        // register the signal-handler for each signal that should be handled
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
                            $this->processOutput->stderr(
                                sprintf(
                                    "[PCNTL ProcessManager] Forcing child process exit for pid %s\n",
                                    $childProcess->getId()
                                )
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

    public function childEarlyExitQueue(): void
    {
        if (!$this->isChildProcess) {
            while (($pid = pcntl_waitpid(-1, $status, WNOHANG)) > 0) {
                $this->earlyExitChildQueue[$pid] = [$pid, pcntl_wexitstatus($status)];
            }
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

        $handleChildExit = function (int $pid, int $status) use ($callbackStack): bool {
            $continueWait = true;
            if ($pid > 0) {
                if (isset($this->childProcesses[$pid])) {
                    unset($this->childProcesses[$pid]);
                }
                if (isset($this->earlyExitChildQueue[$pid])) {
                    unset($this->earlyExitChildQueue[$pid]);
                }
                foreach ($callbackStack as $callback) {
                    if (call_user_func($callback, $status, $pid) === false) {
                        $continueWait = false;
                    }
                }
            }
            return $continueWait;
        };

        // run the callback for all early exit children no matter what
        $waitForMoreToExit = true;
        while (!empty($this->earlyExitChildQueue)) {
            [$pid, $status] = current($this->earlyExitChildQueue);
            $waitForMoreToExit = $waitForMoreToExit && $handleChildExit($pid, $status);
        }

        // only wait for the regular children if desired
        while ($waitForMoreToExit && !empty($this->childProcesses)) {
            $pid = pcntl_wait($status);
            if (!$handleChildExit($pid, $status)) {
                return;
            }
        }
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
