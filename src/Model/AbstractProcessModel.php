<?php declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Model;

use Sweikenb\Library\Pcntl\Api\ProcessInterface;

abstract class AbstractProcessModel implements ProcessInterface
{
    public function __construct(
        protected int $id
    ) {}

    public function getId(): int
    {
        return $this->id;
    }
}