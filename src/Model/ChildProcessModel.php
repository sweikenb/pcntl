<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Model;

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;

class ChildProcessModel extends AbstractProcessModel implements ChildProcessInterface
{
    public function kill(): void
    {
        @posix_kill($this->getId(), SIGKILL);
    }
}
