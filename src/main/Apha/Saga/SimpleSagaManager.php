<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Message\Event;

class SimpleSagaManager extends SagaManager
{
    /**
     * @param string $sagaType
     * @param Event $event
     * @return AssociationValues
     */
    protected function extractAssociationValues(string $sagaType, Event $event): AssociationValues
    {
        return $this->getAssociationValueResolver()->extractAssociationValues($event);
    }

    /**
     * @param string $sagaType
     * @param Event $event
     * @return int
     */
    protected function getSagaCreationPolicy(string $sagaType, Event $event): int
    {
        return SagaCreationPolicy::IF_NONE_FOUND;
    }
}
