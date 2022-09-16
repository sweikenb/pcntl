<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

use Socket;

interface ProcessFactoryInterface
{
    public function createParentProcess(int $pid, ?Socket $ipcSocket): ParentProcessInterface;

    public function createChildProcess(int $pid, ?Socket $ipcSocket): ChildProcessInterface;
}
