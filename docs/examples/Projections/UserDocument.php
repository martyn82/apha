<?php
declare(strict_types = 1);

namespace Apha\Examples\Projections;

use Apha\Message\Event;
use Apha\StateStore\Document;

class UserDocument implements Document
{
    /**
     * @var string
     */
    private $identity;

    /**
     * @var int
     */
    private $version;

    /**
     * @param string $identity
     * @param int $version
     */
    public function __construct(string $identity, int $version)
    {
        $this->identity = $identity;
        $this->version = $version;
    }

    /**
     * @return array
     */
    public function serialize() : array
    {
    }

    /**
     * @param array $serialized
     * @return \Apha\StateStore\Document
     */
    public function deserialize(array $serialized): Document
    {
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->identity;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param Event $event
     * @return void
     */
    public function apply(Event $event)
    {
        $this->version = $event->getVersion();
    }
}
