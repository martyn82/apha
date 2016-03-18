<?php
declare(strict_types = 1);

namespace Apha\Domain;

use Apha\Message\Events;

interface AggregateFactory
{
    /**
     * @param Identity $identity
     * @param Events $events
     * @return AggregateRoot
     */
    public function createAggregate(Identity $identity, Events $events): AggregateRoot;

    /**
     * @return string
     */
    public function getAggregateType(): string;
}