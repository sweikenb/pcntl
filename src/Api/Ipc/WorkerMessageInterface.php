<?php

namespace Sweikenb\Library\Pcntl\Api\Ipc;

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessPoolInterface;

interface WorkerMessageInterface extends MessageInterface
{
    public function execute(ProcessPoolInterface $processPool, ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess): void;
}
