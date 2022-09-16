<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Model;

use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;

class ParentProcessModel extends AbstractProcessModel implements ParentProcessInterface
{
    // The library provides different classes for parent and child to make programming more expressive.
    // Basically you could create just one model which implements all interfaces and achieve the same results.
}
