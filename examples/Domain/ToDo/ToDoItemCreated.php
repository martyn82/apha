<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\ToDo;

use Apha\Domain\Identity;
use Apha\Message\Event;
use JMS\Serializer\Annotation as Serializer;

final class ToDoItemCreated extends Event
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $description;

    /**
     * @Serializer\Type("integer")
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
        $this->setIdentity($identity);
        $this->description = $description;
        $this->expireSeconds = $expireSeconds;
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
