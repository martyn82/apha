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
     * @var AssociationValues
     */
    private $associationValues;

    /**
     * @var Events
     */
    private $changes;

    /**
     * @param Identity $identity
     * @param AssociationValues $associationValues
     */
    public function __construct(Identity $identity, AssociationValues $associationValues = null)
    {
        if ($associationValues != null) {
            $this->associationValues = $associationValues;
        } else {
            $this->associationValues = new AssociationValues([]);
        }

        $this->identity = $identity;
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
     * @return AssociationValues
     */
    final public function getAssociationValues(): AssociationValues
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