<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\Message\Event;
use Apha\Message\Events;

abstract class Saga
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var array
     */
    private $associationValues;

    /**
     * @var Events
     */
    private $changes;

    /**
     * @param Identity $identity
     * @param array $associationValues
     */
    public function __construct(Identity $identity, array $associationValues = [])
    {
        $this->identity = $identity;
        $this->associationValues = $associationValues;
        $this->changes = new Events();
    }

    /**
     * @return Identity
     */
    final public function getId(): Identity
    {
        return $this->identity;
    }

    /**
     * @return array
     */
    final public function getAssociationValues(): array
    {
        return $this->associationValues;
    }

    /**
     * @return Events
     */
    public function getUncommittedChanges(): Events
    {
        return $this->changes;
    }

    /**
     * @return void
     */
    public function markChangesCommitted()
    {
        $this->changes->clear();
    }

    /**
     * @param Event $event
     * @return void
     */
    abstract public function on(Event $event);

    /**
     * @return bool
     */
    abstract public function isActive(): bool;
}