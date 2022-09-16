<?php

namespace Sweikenb\Library\Pcntl\Api\Ipc;

interface WorkerSelectionStrategyInterface
{
    public function configure(array $processIds): void;

    public function getNextWorkerPid(): int;
}
