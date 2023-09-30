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
     * If you specify an $output it will win over the output of the parent process.
     *
     * @throws ProcessException
     */
    public function runProcess(callable $callback, ?ProcessOutputInterface $output = null): ChildProcessInterface;

    /**
     * By default, it waits until all child-processes are returned and calls the optional callback for each of them.
     * The callback will receive the return-status and the pid of the child process as arguments.
     * If the callback returns FALSE (boolean), the method will not wait for further children to exit and returns early,
     * any other or none value (including NULL) will continue to wait if remaining children are present.
     * The callback will be executed before any other registered callback.
     * Only works in the parent-process.
     */
    public function wait(?callable $callback = null): void;

    /**
     * Registers a callback for when a child process gets created. Multiple callbacks can be registered.
     * The callback will receive the corresponding child-process model as the only parameter.
     * Only works in the parent-process.
     */
    public function onThreadCreate(callable $callback): self;

    /**
     * Registers a callback for when a child process exists. Multiple callbacks can be registered.
     * The callback will receive the return-status and the pid of the child process as arguments.
     * If the callback returns FALSE (boolean), the method will not wait for further children to exit and returns
     * early, any other or none value (including NULL) will continue to wait if remaining children are present.
     * Only works in the parent-process.
     */
    public function onThreadExit(callable $callback): self;
}
