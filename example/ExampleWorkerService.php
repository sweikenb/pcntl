<?php

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;

class ExampleWorkerService
{
    public function __invoke(ChildProcessInterface $childProcess, ParentProcessInterface $parentProcess): void
    {
        fwrite(
            STDOUT,
            sprintf(
                "Hello world message handled by worker #%s\n",
                $childProcess->getId()
            )
        );
    }
}
