<?php

namespace Sweikenb\Library\Pcntl\Strategies;

use Sweikenb\Library\Pcntl\Api\Ipc\WorkerSelectionStrategyInterface as WorkerSelectionStrategyInterfaceAlias;
use Sweikenb\Library\Pcntl\Exception\ProcessException;

class RandomWorkerSelectionStrategy implements WorkerSelectionStrategyInterfaceAlias
{
    private array $pids = [];
    private int $max = 0;

    public function configure(array $processIds): void
    {
        if (empty($processIds)) {
            throw new ProcessException('Empty pid-list provided');
        }

        $this->pids = array_map('intval', $processIds);
        $this->max = count($this->pids) - 1;
    }

    public function getNextWorkerPid(): int
    {
        $nextIndex = 0;
        if ($this->max !== 0) {
            $nextIndex = mt_rand(0, $this->max);
        }
        return $this->pids[$nextIndex];
    }
}
