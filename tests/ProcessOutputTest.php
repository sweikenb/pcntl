<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Sweikenb\Library\Pcntl\ProcessOutput;
use Tests\TestHelper\TempFileTrait;

class ProcessOutputTest extends TestCase
{
    use TempFileTrait;

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessOutput::stdout
     */
    public function testStdout(): void
    {
        $filepathPm = sprintf("%s/stdout", self::TEST_DIR);
        $output = new ProcessOutput(stdOutFile: $filepathPm);

        $output->stdout("Some message without line break.");
        $output->stdout("Some message WITH line break.\n");
        $output->stdout("Some final thoughts");

        $this->assertSame(
            file_get_contents($filepathPm),
            <<<ACTUAL
Some message without line break.Some message WITH line break.
Some final thoughts
ACTUAL
        );
    }

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessOutput::stdout
     */
    public function testStdoutAppend(): void
    {
        $filepath = sprintf("%s/stdout", self::TEST_DIR);
        file_put_contents($filepath, "EXISTING STDOUT CONTENT\n\n");
        $output = new ProcessOutput(stdOutFile: $filepath);

        $output->stdout("Some message without line break.");
        $output->stdout("Some message WITH line break.\n");
        $output->stdout("Some final thoughts");

        $this->assertSame(
            file_get_contents($filepath),
            <<<ACTUAL
EXISTING STDOUT CONTENT

Some message without line break.Some message WITH line break.
Some final thoughts
ACTUAL
        );
    }

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessOutput::stderr
     */
    public function testStderr(): void
    {
        $filepath = sprintf("%s/stderr", self::TEST_DIR);
        $output = new ProcessOutput(stdErrFile: $filepath);

        $output->stderr("Some message without line break.");
        $output->stderr("Some message WITH line break.\n");
        $output->stderr("Some final thoughts");

        $this->assertSame(
            file_get_contents($filepath),
            <<<ACTUAL
Some message without line break.Some message WITH line break.
Some final thoughts
ACTUAL
        );
    }

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessOutput::stderr
     */
    public function testStderrAppend(): void
    {
        $filepath = sprintf("%s/stderr", self::TEST_DIR);
        file_put_contents($filepath, "EXISTING STDERR CONTENT\n\n");
        $output = new ProcessOutput(stdErrFile: $filepath);

        $output->stderr("Some message without line break.");
        $output->stderr("Some message WITH line break.\n");
        $output->stderr("Some final thoughts");

        $this->assertSame(
            file_get_contents($filepath),
            <<<ACTUAL
EXISTING STDERR CONTENT

Some message without line break.Some message WITH line break.
Some final thoughts
ACTUAL
        );
    }
}
