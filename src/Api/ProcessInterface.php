<?php
declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Api;

/**
 * Interface ProcessInterface
 *
 * @api
 */
interface ProcessInterface
{
    /**
     * Returns the pricess id.
     *
     * @return int
     */
    public function getId(): int;
}