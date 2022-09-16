<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

use Sweikenb\Library\Pcntl\Exception\ProcessException;

interface ProcessManagerInterface
{
    /**
     * Returns the instance of the main process.
     */
    public function getMainProcess(): ParentProcessInterface;

    /**
     * Executes the provided callback within a child process and does not wait for it to finish.
     *
     * @throws ProcessException
     */
    public function runProcess(callable $callback): ChildProcessInterface;

    /**
     * Waits until all child-processes are returned and calls the optional callback for each process that returns.
     * The callback will receive the return-status and the pid of the child process.
     *
     * Only works in the parent-process.
     */
    public function wait(?callable $callback = null): ProcessManagerInterface;
}
