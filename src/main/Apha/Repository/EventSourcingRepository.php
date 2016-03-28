<?php
declare(strict_types = 1);

namespace Apha\Repository;

use Apha\Domain\AggregateFactory;
use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;
use Apha\EventStore\ConcurrencyException;
use Apha\EventStore\EventStore;

class EventSourcingRepository implements Repository
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var AggregateFactory
     */
    private $factory;

    /**
     * @param AggregateFactory $factory
     * @param EventStore $eventStore
     */
    public function __construct(AggregateFactory $factory, EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
        $this->factory = $factory;
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
            $this->factory->getAggregateType(),
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
        return $this->factory->createAggregate($aggregateIdentity, $events);
    }
}