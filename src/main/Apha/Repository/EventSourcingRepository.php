<?php
declare(strict_types = 1);

namespace Apha\Repository;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;
use Apha\EventStore\ConcurrencyException;
use Apha\EventStore\EventStore;

class EventSourcingRepository implements Repository
{
    /**
     * @var string
     */
    private $aggregateType;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @param string $aggregateType
     * @param EventStore $eventStore
     */
    public function __construct(string $aggregateType, EventStore $eventStore)
    {
        $this->aggregateType = $aggregateType;
        $this->eventStore = $eventStore;
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @param int $expectedPlayHead
     * @return void
     * @throws ConcurrencyException
     */
    public function store(AggregateRoot $aggregateRoot, int $expectedPlayHead = -1)
    {
        $this->eventStore->save(
            $aggregateRoot->getId(),
            $this->aggregateType,
            $aggregateRoot->getUncommittedChanges(),
            $expectedPlayHead
        );

        $aggregateRoot->markChangesCommitted();
    }

    /**
     * @param Identity $aggregateIdentity
     * @return AggregateRoot
     */
    public function findById(Identity $aggregateIdentity): AggregateRoot
    {
        $events = $this->eventStore->getEventsForAggregate($aggregateIdentity);

        $aggregateType = $this->aggregateType;
        return $aggregateType::reconstruct($events);
    }
}