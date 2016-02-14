<?php
declare(strict_types = 1);

namespace Apha\Replay;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;
use Apha\EventStore\EventStore;
use Apha\MessageBus\EventBus;

class AggregateEventPlayer extends EventPlayer
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param EventBus $eventBus
     * @param EventStore $eventStore
     */
    public function __construct(EventBus $eventBus, EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
        parent::__construct($eventBus);
    }

    /**
     * @param AggregateRoot $aggregate
     */
    public function replayEventsByAggregate(AggregateRoot $aggregate)
    {
        $this->replayEventsByAggregateId($aggregate->getId());
    }

    /**
     * @param Identity $aggregateId
     */
    public function replayEventsByAggregateId(Identity $aggregateId)
    {
        $events = $this->eventStore->getEventsForAggregate($aggregateId);
        $this->play($events);
    }
}