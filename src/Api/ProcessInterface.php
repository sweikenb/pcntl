<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

use Socket;
use Sweikenb\Library\Pcntl\Api\Ipc\MessageInterface;

interface ProcessInterface
{
    public function getId(): int;

    public function setIpcSocket(?Socket $socket): self;

    public function getIpcSocket(): ?Socket;

    public function sendMessage(MessageInterface $message): bool;

    public function getNextMessage(bool $wait = true): ?MessageInterface;
}
