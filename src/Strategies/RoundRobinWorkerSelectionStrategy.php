<?php

namespace Sweikenb\Library\Pcntl\Strategies;

use Sweikenb\Library\Pcntl\Api\Ipc\WorkerSelectionStrategyInterface;
use Sweikenb\Library\Pcntl\Exception\ProcessException;

class RoundRobinWorkerSelectionStrategy implements WorkerSelectionStrategyInterface
{
    private ?int $lastPid = null;
    private array $pids = [];

    public function configure(array $processIds): void
    {
        if (empty($processIds)) {
            throw new ProcessException('Empty pid-list provided');
        }

        $this->pids = array_map('intval', $processIds);
        $this->lastPid = -1;
    }

    public function getNextWorkerPid(): int
    {
        if (count($this->pids) === 0) {
            throw new ProcessException(
                'Can not provide next worker pid as the strategy is not properly configured yet'
            );
        }

        // move to the next
        $this->lastPid++;
        if (!isset($this->pids[$this->lastPid])) {
            // rewind
            $this->lastPid = 0;
        }

        // return pid to use
        return $this->pids[$this->lastPid];
    }
}
