<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\Message\Event;
use Apha\Message\Events;
use JMS\Serializer\Annotation as Serializer;

abstract class Saga
{
    /**
     * @Serializer\Type("Apha\Domain\Identity")
     * @var Identity
     */
    private $identity;

    /**
     * @Serializer\Type("Apha\Saga\AssociationValues")
     * @var AssociationValues
     */
    private $associationValues;

    /**
     * @param Identity $identity
     * @param AssociationValues $associationValues
     */
    public function __construct(Identity $identity, AssociationValues $associationValues)
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
     * @return AssociationValues
     */
    final public function getAssociationValues(): AssociationValues
    {
        return $this->associationValues;
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
