<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Model;

use Socket;
use Sweikenb\Library\Pcntl\Api\Ipc\MessageInterface;
use Sweikenb\Library\Pcntl\Api\ProcessInterface;
use Sweikenb\Library\Pcntl\Exception\ProcessException;

abstract class AbstractProcessModel implements ProcessInterface
{
    public function __construct(
        protected readonly int $id,
        protected ?Socket $ipcSocket
    ) {
    }

    public function __destruct()
    {
        if ($this->ipcSocket) {
            @socket_shutdown($this->ipcSocket);
            @socket_close($this->ipcSocket);
            $this->ipcSocket = null;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setIpcSocket(?Socket $socket): self
    {
        $this->ipcSocket = $socket;
        return $this;
    }

    public function getIpcSocket(): ?Socket
    {
        return $this->ipcSocket;
    }

    public function sendMessage(MessageInterface $message): bool
    {
        // send message and capture the next response-message
        $socket = $this->getIpcSocket();
        if ($socket) {
            $buffer = serialize($message);
            $buffer = sprintf("%s#%s", strlen($buffer), $buffer);
            while (($bufferLength = strlen($buffer)) > 0) {
                $written = socket_write($socket, $buffer, strlen($buffer));
                if ($written === false) {
                    throw new ProcessException(socket_strerror(socket_last_error($socket)));
                }
                if ($written < $bufferLength) {
                    $buffer = substr($buffer, $written);
                } else {
                    $buffer = '';
                }
            }
            return true;
        }
        return false;
    }

    public function getNextMessage(bool $wait = true): ?MessageInterface
    {
        $socket = $this->getIpcSocket();
        if (!$socket) {
            return null;
        }

        $length = '';
        $buffer = null;
        while (true) {
            if ($buffer === null) {
                $char = socket_read($socket, 1);
                if ($char === false) {
                    throw new ProcessException(socket_strerror(socket_last_error($socket)));
                }
                if ($char === '') {
                    if ($wait) {
                        // unblock the system
                        usleep(1000);
                        continue;
                    }
                    return null;
                }
                if ($char === '#') {
                    $length = intval($length);
                    $buffer = '';
                } else {
                    if (!is_numeric($char)) {
                        throw new ProcessException('Unexpected char, can not read message.');
                    }
                    $length .= $char;
                }
                continue;
            }
            $buffer = socket_read($socket, $length);
            $message = @unserialize($buffer);
            if ($message === false) {
                throw new ProcessException('Can not unserialize message.');
            }
            if ($message instanceof MessageInterface) {
                return $message;
            }
            throw new ProcessException('Invalid message received.');
        }
    }
}
