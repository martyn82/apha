<?php
declare(strict_types = 1);

namespace Apha\Message;

use Apha\Domain\Identity;
use JMS\Serializer\Annotation as Serializer;

abstract class Event extends Message
{
    /**
     * @Serializer\Type("integer")
     * @var int
     */
    private $version = 0;

    /**
     * @Serializer\Type("Apha\Domain\Identity")
     * @var Identity
     */
    protected $identity;

    /**
     * @return string
     */
    public static function getName(): string
    {
        $eventClassParts = explode('\\', static::class);
        return end($eventClassParts);
    }

    /**
     * @return string
     */
    final public function getEventName(): string
    {
        return static::getName();
    }

    /**
     * @param Identity $identity
     */
    protected function setIdentity(Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return Identity
     */
    public function getIdentity(): Identity
    {
        return $this->identity;
    }

    /**
     * @return int
     */
    final public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    final public function setVersion(int $version)
    {
        $this->version = $version;
    }
}