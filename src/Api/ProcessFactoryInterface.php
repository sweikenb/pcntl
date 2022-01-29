<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

interface ProcessFactoryInterface
{
    public function createParentProcess(int $pid): ParentProcessInterface;

    public function createChildProcess(int $pid): ChildProcessInterface;
}