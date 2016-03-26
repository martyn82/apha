<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\ToDo;

use Apha\Domain\Identity;

final class CreateToDoItem extends \Apha\Message\Command
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $expireSeconds;

    /**
     * @param Identity $identity
     * @param string $description
     * @param int $expireSeconds
     */
    public function __construct(Identity $identity, string $description, int $expireSeconds)
    {
        $this->identity = $identity;
        $this->description = $description;
        $this->expireSeconds = $expireSeconds;
    }

    /**
     * @return Identity
     */
    public function getIdentity(): Identity
    {
        return $this->identity;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getExpireSeconds(): int
    {
        return $this->expireSeconds;
    }
}
