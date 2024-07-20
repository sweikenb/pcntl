<?php declare(strict_types=1, ticks=1);

namespace Sweikenb\Library\Pcntl;

use Sweikenb\Library\Pcntl\Api\ProcessOutputInterface;

class ProcessOutput implements ProcessOutputInterface
{
    private $stdOut;
    private $stdErr;

    public function __construct(?string $stdOutFile = null, ?string $stdErrFile = null)
    {
        $this->stdOut = $stdOutFile ? fopen($stdOutFile, 'ab') : null;
        $this->stdErr = $stdErrFile ? fopen($stdErrFile, 'ab') : null;
    }

    public function __destruct()
    {
        if ($this->stdOut !== null) {
            @fclose($this->stdOut);
        }
        if ($this->stdErr !== null) {
            @fclose($this->stdErr);
        }
    }

    public function stdout(string $msg): ProcessOutputInterface
    {
        fwrite($this->stdOut ?? STDOUT, $msg);
        return $this;
    }

    public function stderr(string $msg): ProcessOutputInterface
    {
        fwrite($this->stdErr ?? STDERR, $msg);
        return $this;
    }
}
