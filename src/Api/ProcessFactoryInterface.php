<?php
declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

/**
 * Interface ProcessFactoryInterface
 *
 * @api
 */
interface ProcessFactoryInterface
{
    /**
     * @param int $pid
     *
     * @return \Sweikenb\Library\Pcntl\Api\ParentProcessInterface
     */
    public function createParentProcess(int $pid): ParentProcessInterface;

    /**
     * @param int $pid
     *
     * @return \Sweikenb\Library\Pcntl\Api\ChildProcessInterface
     */
    public function createChildProcess(int $pid): ChildProcessInterface;
}