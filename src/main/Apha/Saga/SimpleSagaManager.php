<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Message\Event;

class SimpleSagaManager extends SagaManager
{
    /**
     * @param string $sagaType
     * @param Event $event
     * @return AssociationValue
     */
    protected function extractAssociationValue(string $sagaType, Event $event): AssociationValue
    {
        // go naive
        return new AssociationValue('identity', $event->getIdentity()->getValue());
    }
}