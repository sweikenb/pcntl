<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Factory;

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Api\ProcessFactoryInterface;
use Sweikenb\Library\Pcntl\Model\ChildProcessModel;
use Sweikenb\Library\Pcntl\Model\ParentProcessModel;

class ProcessFactory implements ProcessFactoryInterface
{
    public function createParentProcess(int $pid): ParentProcessInterface
    {
        return new ParentProcessModel($pid);
    }

    public function createChildProcess(int $pid): ChildProcessInterface
    {
        return new ChildProcessModel($pid);
    }
}