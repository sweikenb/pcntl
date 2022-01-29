<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Model;

use Sweikenb\Library\Pcntl\Api\ChildProcessInterface;

class ChildProcessModel extends AbstractProcessModel implements ChildProcessInterface
{
    // The library provides different classes for parent and child to make programming more expressive.
    // Basically you could create just one model which implements all interfaces and achieve the same results.
}