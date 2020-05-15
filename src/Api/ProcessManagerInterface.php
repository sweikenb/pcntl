<?php
declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

/**
 * Interface ProcessManagerInterface
 *
 * @api
 */
interface ProcessManagerInterface
{
    /**
     * Returns the instance of the main process.
     *
     * @return \Sweikenb\Library\Pcntl\Api\ParentProcessInterface
     */
    public function getMainProcess(): ParentProcessInterface;

    /**
     * Executes the provided callback within a child process and does not wait for it to finish.
     *
     * @param callable $callback
     *
     * @return \Sweikenb\Library\Pcntl\Api\ChildProcessInterface|null Returns a child-process in case of success
     * @throws \Sweikenb\Library\Pcntl\Exception\ProcessException
     */
    public function runProcess(callable $callback): ChildProcessInterface;

    /**
     * Waits until all child-processes are returned and calls the optional callback for each process that returns.
     * The callback will receive the return-status and the pid of the child process.
     *
     * Only works in the parent-process.
     *
     * @param callable $callback Optional callback that gets executed after each child-processes return.
     *
     * @return \Sweikenb\Library\Pcntl\Api\ProcessManagerInterface
     */
    public function wait(callable $callback = null): ProcessManagerInterface;
}
