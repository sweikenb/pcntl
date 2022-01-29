<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Event;

use Symfony\Contracts\EventDispatcher\Event;

class ProcessManagerEvent extends Event
{
    public function __construct(
        private string $eventName,
        private ?int $processId = null
    ) {}

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getProcessId(): ?int
    {
        return $this->processId;
    }
}