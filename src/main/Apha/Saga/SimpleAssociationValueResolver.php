<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Message\Event;

class SimpleAssociationValueResolver implements AssociationValueResolver
{
    /**
     * @param Event $event
     * @return AssociationValues
     */
    public function extractAssociationValues(Event $event): AssociationValues
    {
        return new AssociationValues([
            new AssociationValue('identity', $event->getIdentity()->getValue())
        ]);
    }
}