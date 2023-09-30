<?php

namespace Sweikenb\Library\Pcntl\Api;

interface ProcessOutputInterface
{
    /**
     * Writes the given $msg to the configured STDOUT resource
     */
    public function stdout(string $msg): self;

    /**
     * Writes the given $msg to the configured STDERR resource
     */
    public function stderr(string $msg): self;
}
