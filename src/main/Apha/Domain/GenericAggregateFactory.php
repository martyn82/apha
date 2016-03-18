<?php
declare(strict_types = 1);

namespace Apha\Domain;

use Apha\Message\Events;

class GenericAggregateFactory implements AggregateFactory
{
    /**
     * @var string
     */
    private $aggregateType;

    /**
     * @param string $aggregateType
     */
    public function __construct(string $aggregateType)
    {
        $this->aggregateType = $aggregateType;
    }

    /**
     * @param Identity $identity
     * @param Events $events
     * @return AggregateRoot
     */
    public function createAggregate(Identity $identity, Events $events): AggregateRoot
    {
        /* @var $aggregateType AggregateRoot */
        $aggregateType = $this->aggregateType;
        return $aggregateType::reconstruct($events);
    }

    /**
     * @return string
     */
    public function getAggregateType(): string
    {
        return $this->aggregateType;
    }
}