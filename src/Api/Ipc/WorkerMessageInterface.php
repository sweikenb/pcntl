<?php

namespace Sweikenb\Library\Pcntl\Api\Ipc;

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;

interface WorkerMessageInterface extends MessageInterface
{
    public function execute(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess): void;
}
