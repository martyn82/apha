<?php
declare(strict_types = 1);

namespace Apha\Message;

use JMS\Serializer\Annotation as Serializer;

abstract class Event implements Message
{
    /**
     * @Serializer\Type("integer")
     * @var int
     */
    private $version;

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
    public function getEventName(): string
    {
        return static::getName();
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