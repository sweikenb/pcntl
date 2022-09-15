<?php

namespace Sweikenb\Library\Pcntl\Model\Ipc;

use ReflectionClass;
use ReflectionException;
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\Ipc\WorkerMessageInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Exception\ProcessException;

class WorkerMessageModel extends MessageModel implements WorkerMessageInterface
{
    /**
     * @throws ProcessException
     * @throws ReflectionException
     */
    public function execute(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess): void
    {
        if (class_exists($this->getPayload())) {
            $ref = new ReflectionClass($this->getPayload());
            $worker = $ref->newInstance();
            if (is_callable($worker)) {
                $worker($childProcess, $parentProcess);
            } else {
                throw new ProcessException('Provided class is not invokable!');
            }
        } else {
            throw new ProcessException('Can not execute payload class!');
        }
    }
}
