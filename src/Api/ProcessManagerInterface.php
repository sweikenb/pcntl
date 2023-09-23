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
     * Only works in the parent-process.
     * By default, it waits until all child-processes are returned and calls the optional callback for each of them.
     * The callback will receive the return-status and the pid of the child process as arguments.
     * If the callback returns FALSE (boolean), the method will not wait for further children to exit and returns early,
     * any other or none value (including NULL) will continue to wait if remaining children are present.
     */
    public function wait(?callable $callback = null): void;
}
