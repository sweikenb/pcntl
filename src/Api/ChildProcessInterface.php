<?php
declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

/**
 * Interface ChildProcessInterface
 *
 * @api
 */
interface ChildProcessInterface extends ProcessInterface
{
    // The library provides different classes for parent and child to make programming more expressiv.
    // Basically you could create just one model wich implements all interfaces and achive the same results.
}