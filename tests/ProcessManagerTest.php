<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;
use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;
use Sweikenb\Library\Pcntl\Factory\MessageFactory;
use Sweikenb\Library\Pcntl\ProcessManager;
use Sweikenb\Library\Pcntl\ProcessOutput;
use Tests\TestHelper\TempFileTrait;

class ProcessManagerTest extends TestCase
{
    use TempFileTrait;

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::getMainProcess
     */
    public function testGetMainProcess(): void
    {
        $pm = new ProcessManager();
        $this->assertSame(posix_getpid(), $pm->getMainProcess()->getId());
    }

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::runProcess
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::wait
     */
    public function testRunProcess(): void
    {
        $pm = new ProcessManager();

        $parentPid = $pm->getMainProcess()->getId();
        for ($i = 0; $i < 5; $i++) {
            $pm->runProcess(
                function (ChildProcessInterface $child, ParentProcessInterface $parent) use ($parentPid) {
                    return $child->getId() === posix_getpid()
                        && $parent->getId() === $parentPid
                        && touch($this->getPidFile($child->getId()));
                }
            );
        }

        $pm->wait(function (int $status, int $pid) {
            $this->assertSame(0, $status);
            $this->assertFileExists($this->getPidFile($pid));
        });
    }

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::runProcess
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::wait
     */
    public function testRunProcessWithErrors(): void
    {
        $pm = new ProcessManager();

        for ($i = 0; $i < 6; $i++) {
            $pm->runProcess(fn() => $i > 2);
        }

        $expectedSuccess = 3;
        $actualSuccess = 0;

        $expectedErrors = 3;
        $actualErrors = 0;

        $pm->wait(function (int $status) use (&$actualSuccess, &$actualErrors) {
            if ($status === 0) {
                $actualSuccess++;
            } else {
                $actualErrors++;
            }
        });

        $this->assertSame($expectedSuccess, $actualSuccess);
        $this->assertSame($expectedErrors, $actualErrors);
    }

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::runProcess
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::wait
     */
    public function testRunProcessWithExceptions(): void
    {
        $stderrFilePm = sprintf("%s/stderr-pm", self::TEST_DIR);
        $pmOutput = new ProcessOutput(stdErrFile: $stderrFilePm);

        $stderrFileRun = sprintf("%s/stderr-run", self::TEST_DIR);
        $runOutput = new ProcessOutput(stdErrFile: $stderrFileRun);

        $pm = new ProcessManager(processOutput: $pmOutput);

        $errMsg = 'Some Test Error';

        $pm->runProcess(fn() => throw new Exception($errMsg));
        $pm->runProcess(fn() => throw new Exception($errMsg), $runOutput);

        $pm->wait(function (int $status) use (&$actualSuccess, &$actualErrors) {
            $this->assertGreaterThan(0, $status);
        });

        $this->assertSame(
            file_get_contents($stderrFilePm),
            sprintf("[PCNTL ProcessManager] Child process exception: %s\n", $errMsg)
        );

        $this->assertSame(
            file_get_contents($stderrFileRun),
            sprintf("[PCNTL ProcessManager] Child process exception: %s\n", $errMsg)
        );
    }

    /**
     * @covers \Sweikenb\Library\Pcntl\Api\ProcessInterface::sendMessage
     * @covers \Sweikenb\Library\Pcntl\Api\ProcessInterface::getNextMessage
     */
//    public function testIPC(): void
//    {
//        $pm = new ProcessManager();
//        $factory = new MessageFactory();
//
//        $childs = [];
//        /* @var array<int, ChildProcessInterface> $childs */
//
//        for ($i = 0; $i < 5; $i++) {
//            $childs[$i] = $pm->runProcess(
//                function (ChildProcessInterface $process, ParentProcessInterface $parentProcess) use ($i, $factory) {
//                    $message = $parentProcess->getNextMessage();
//                    $parentProcess->sendMessage(
//                        $factory->create(sprintf('answer from #%d', $i), 'hello')
//                    );
//                    return $message !== null
//                        && $message->getTopic() === sprintf("hello my child %d", $i)
//                        && $message->getPayload() === 'hello';
//                }
//            );
//            $childs[$i]->sendMessage($factory->create(sprintf("hello my child %d", $i), 'hello'));
//        }
//
//        foreach ($childs as $i => $child) {
//            $message = $child->getNextMessage();
//            $this->assertNotNull($message);
//            $this->assertSame(sprintf("answer from #%s", $i), $message->getTopic());
//            $this->assertSame('hello', $message->getPayload());
//
//            $this->assertNull($child->getNextMessage(false));
//        }
//
//        $pm->wait(fn(int $status) => $this->assertSame(0, $status));
//    }

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::wait
     */
    public function testWait(): void
    {
        $pm = new ProcessManager();

        $numChilds = 100;
        for ($i = 1; $i <= $numChilds; $i++) {
            $pm->runProcess(function () use ($i) {
                usleep($i * 10);
                return true;
            });
        }

        $numClosed = 0;
        $numWaitLoops = 0;
        while ($numChilds > $numClosed) {
            $numWaitLoops++;
            $pm->wait(function () use (&$numClosed) {
                $numClosed++;
                return $numClosed > 50;
            });
        }

        $this->assertSame(51, $numWaitLoops);
    }

    /**
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::onThreadExit
     * @covers \Sweikenb\Library\Pcntl\ProcessManager::wait
     */
    public function testOnChildExit(): void
    {
        $pm = new ProcessManager();

        $exitCalled = 0;
        $waitCalled = 0;

        $pm->onThreadExit(function () use(&$exitCalled, $waitCalled){
            $exitCalled++;
            $this->assertGreaterThanOrEqual($waitCalled, $exitCalled);
        });
        for ($i = 1; $i <= 20; $i++) {
            $pm->runProcess(fn() => usleep(1000));
        }
        $pm->wait(function () use($exitCalled, &$waitCalled){
            $waitCalled++;
            $this->assertLessThan($waitCalled, $exitCalled);
        });
    }
}
