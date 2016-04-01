<?php
declare(strict_types = 1);

namespace Apha\Replay;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;
use Apha\EventHandling\EventBus;
use Apha\EventStore\EventStore;
use Apha\Message\Events;

class AggregateEventPlayer
{
    /**
     * @var EventPlayer
     */
    private $eventPlayer;

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
        $this->eventPlayer = new EventPlayer($eventBus);
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
        $this->eventPlayer->play($events);
    }

    /**
     * @param Identity $aggregateId
     * @param int $version
     */
    public function playToVersion(Identity $aggregateId, int $version)
    {
        $events = $this->eventStore->getEventsForAggregate($aggregateId);
        $partialHistory = [];

        /* @var $event \Apha\Message\Event */
        foreach ($events->getIterator() as $event) {
            if ($event->getVersion() > $version) {
                break;
            }

            $partialHistory[] = $event;
        }

        $this->eventPlayer->play(new Events($partialHistory));
    }
}