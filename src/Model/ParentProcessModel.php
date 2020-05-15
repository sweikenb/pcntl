<?php
declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Model;

use Sweikenb\Library\Pcntl\Api\ParentProcessInterface;

class ParentProcessModel extends AbstractProcessModel implements ParentProcessInterface
{
    // The library provides different classes for parent and child to make programming more expressiv.
    // Basically you could create just one model wich implements all interfaces and achive the same results.
}