<?php
namespace Sweikenb\Library\Pcntl;

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessManagerInterface;

class PoolManager
{
    const DEFAULT_KILL_TIMEOUT = 1.0;
    private ProcessManagerInterface $pm;
    private bool $interrupted = false;
    private array $processes = [];

    public function __construct(?ProcessManagerInterface $processManager = null,)
    {
        // create the process manager instance if not provided
        $this->pm = $processManager ?? new ProcessManager();

        // register/override interrupt handler for the main-process
        pcntl_signal(SIGINT, [$this, 'handleInterrupt']);

        // register events
        $this->pm->onThreadCreate(function (ChildProcessInterface $process) {
            $this->processes[$process->getId()] = $process;
        });
        $this->pm->onThreadExit(function (int $status, int $pid) {
            unset($this->processes[$pid]);
        });
    }

    public function handleInterrupt(): void
    {
        $this->interrupted = true;
        $this->pm->sendSignalToChildren(SIGTERM);
    }

    public function execute(int $poolSize, callable $mainLoop, callable $processLoop, ?float $killTimeout = null): int
    {
        while (!$this->interrupted) {
            // ensure we have enough threads
            $this->pm->wait(block: false);
            $missing = $poolSize - count($this->processes);
            for ($i = 0; $i < $missing; $i++) {
                $this->pm->runProcess($processLoop);
            }

            // call the main-loop
            $this->pm->wait(block: false);
            call_user_func($mainLoop, $this->processes, $this->pm);
        }

        // wait for children to exit after sending the SIGTERM by the SIGINT handler
        // send a SIGKILL to forcefully terminate the child after the kill-timeout is reached
        $status = 0;
        $waitStart = microtime(true);
        while (!empty($this->processes)) {
            if ((microtime(true) - $waitStart) >= ($killTimeout ?? self::DEFAULT_KILL_TIMEOUT)) {
                $this->pm->sendSignalToChildren(SIGKILL);
                $status = 1;
                break;
            }
            $this->pm->unblock();
        }

        // final wait until all children exited after the KILL
        $this->pm->wait();

        return $status;
    }
}
