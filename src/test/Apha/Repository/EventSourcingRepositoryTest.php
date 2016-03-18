<?php
declare(strict_types = 1);

namespace Apha\Repository;
use Apha\Domain\AggregateFactory;
use Apha\Domain\AggregateRoot;
use Apha\Domain\GenericAggregateFactory;
use Apha\Domain\Identity;
use Apha\EventStore\EventStore;
use Apha\Message\Event;
use Apha\Message\Events;

class EventSourcingRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EventStore
     */
    private function createEventStore(): EventStore
    {
        return $this->getMockBuilder(EventStore::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param string $type
     * @return AggregateFactory
     */
    private function createAggregateFactory(string $type): AggregateFactory
    {
        return new GenericAggregateFactory($type);
    }

    /**
     * @test
     */
    public function storeSavesUncommittedEvents()
    {
        $events = new Events([
            $this->getMockBuilder(Event::class)->getMock()
        ]);

        $identity = Identity::createNew();

        $aggregate = $this->getMockBuilder(AggregateRoot::class)
            ->disableOriginalConstructor()
            ->getMock();

        $aggregate->expects(self::once())
            ->method('getUncommittedChanges')
            ->willReturn($events);

        $aggregate->expects(self::once())
            ->method('markChangesCommitted');

        $aggregate->expects(self::any())
            ->method('getId')
            ->willReturn($identity);

        $aggregateType = get_class($aggregate);

        $factory = $this->createAggregateFactory($aggregateType);
        $eventStore = $this->createEventStore();

        $eventStore->expects(self::once())
            ->method('save')
            ->with($identity, $aggregateType, $events, -1);

        $repository = new EventSourcingRepository($factory, $eventStore);
        $repository->store($aggregate);
    }

    /**
     * @test
     */
    public function findByIdRetrievesAggregateByIdentity()
    {
        $aggregate = new EventSourcingRepositoryTest_AggregateRoot();
        $aggregateType = get_class($aggregate);
        $factory = $this->createAggregateFactory($aggregateType);

        $identity = Identity::createNew();

        $events = new Events([
            new EventSourcingRepositoryTest_Event()
        ]);

        $eventStore = $this->createEventStore();

        $eventStore->expects(self::once())
            ->method('getEventsForAggregate')
            ->with($identity)
            ->willReturn($events);

        $repository = new EventSourcingRepository($factory, $eventStore);
        $repository->findById($identity);
    }
}

final class EventSourcingRepositoryTest_Event extends Event
{
}

final class EventSourcingRepositoryTest_AggregateRoot extends AggregateRoot
{
    /**
     * @var Identity
     */
    private $id;

    /**
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return Identity
     */
    public function getId(): Identity
    {
        return $this->id;
    }

    /**
     * @param EventSourcingRepositoryTest_Event $event
     */
    public function applyEventSourcingRepositoryTest_Event(EventSourcingRepositoryTest_Event $event)
    {
    }
}