<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

interface ChildProcessInterface extends ProcessInterface
{
    public function kill(): void;
}
