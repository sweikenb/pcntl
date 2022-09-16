<?php

namespace Sweikenb\Library\Pcntl\Model\Ipc;

use Exception;
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\Ipc\WorkerMessageInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessPoolInterface;

class WorkerMessageModel extends MessageModel implements WorkerMessageInterface
{
    public function execute(
        ProcessPoolInterface $processPool,
        ChildProcessInterface $childProcess,
        ParentProcessInterface $parentProcess
    ): void {
        try {
            $classname = $this->getPayload();
            call_user_func(
                $processPool->getInvocationBuilder(),
                $classname,
                $childProcess,
                $parentProcess
            );
        } catch (Exception $e) {
            fwrite(
                STDERR,
                sprintf(
                    "WORKER(%d) ERROR: %s (%s:%s)\n",
                    $childProcess->getId(),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }
    }
}
