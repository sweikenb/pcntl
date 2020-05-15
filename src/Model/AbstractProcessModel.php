<?php
declare(strict_types=1);

namespace Sweikenb\Library\Pcntl\Model;

use Sweikenb\Library\Pcntl\Api\ProcessInterface;

/**
 * Class AbstractProcessModel
 *
 * @package Sweikenb\Library\Pcntl\Model
 */
abstract class AbstractProcessModel implements ProcessInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * AbstractProcessModel constructor.
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}