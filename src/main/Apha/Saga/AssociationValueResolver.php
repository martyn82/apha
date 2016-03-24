<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Message\Event;

interface AssociationValueResolver
{
    /**
     * @param Event $event
     * @return AssociationValues
     */
    public function extractAssociationValues(Event $event): AssociationValues;
}