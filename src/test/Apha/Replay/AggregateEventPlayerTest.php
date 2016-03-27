<?php
declare(strict_types = 1);

namespace Apha\Replay;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;
use Apha\EventHandling\EventBus;
use Apha\EventStore\EventClassMap;
use Apha\EventStore\EventDescriptor;
use Apha\EventStore\EventStore;
use Apha\EventStore\Storage\EventStorage;
use Apha\Message\Event;
use Apha\Message\Events;
use Apha\Serializer\JsonSerializer;
use Apha\Serializer\Serializer;

/**
 * @group replay
 */
class AggregateEventPlayerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Serializer
     */
    private function getSerializer()
    {
        return new JsonSerializer();
    }

    /**
     * @return EventClassMap
     */
    private function getEventClassMap()
    {
        return new EventClassMap([
            AggregateEventPlayerTest_Event::class
        ]);
    }

    /**
     * @return EventStorage
     */
    private function getEventStorage()
    {
        return $this->getMockBuilder(EventStorage::class)
            ->getMockForAbstractClass();
    }

    /**
     * @return EventBus
     */
    private function getEventBus()
    {
        return $this->getMockBuilder(EventBus::class)
            ->getMockForAbstractClass();
    }

    /**
     * @param EventBus $eventBus
     * @param EventStorage $eventStorage
     * @param Serializer $serializer
     * @param EventClassMap $eventClassMap
     * @return EventStore
     */
    private function getEventStore(
        EventBus $eventBus,
        EventStorage $eventStorage,
        Serializer $serializer,
        EventClassMap $eventClassMap
    )
    {
        return $this->getMockBuilder(EventStore::class)
            ->setConstructorArgs([$eventBus, $eventStorage, $serializer, $eventClassMap])
            ->getMock();
    }

    /**
     * @return AggregateRoot
     */
    private function getAggregate()
    {
        return $this->getMockBuilder(AggregateRoot::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }

    /**
     * @test
     */
    public function replayEventsByAggregateReplaysEventsFromAggregate()
    {
        $serializer = $this->getSerializer();
        $eventClassMap = $this->getEventClassMap();
        $eventStorage = $this->getEventStorage();
        $eventBus = $this->getEventBus();
        $eventStore = $this->getEventStore($eventBus, $eventStorage, $serializer, $eventClassMap);

        $aggregate = $this->getAggregate();
        $aggregateId = Identity::createNew();

        $basicEvents = [
            new AggregateEventPlayerTest_Event(),
            new AggregateEventPlayerTest_Event()
        ];

        $events = new Events($basicEvents);

        $eventDescriptors = [
            EventDescriptor::record(
                $aggregateId->getValue(),
                get_class($aggregate),
                $basicEvents[0]->getEventName(),
                $serializer->serialize($basicEvents[0]),
                1
            ),
            EventDescriptor::record(
                $aggregateId->getValue(),
                get_class($aggregate),
                $basicEvents[0]->getEventName(),
                $serializer->serialize($basicEvents[0]),
                2
            )
        ];

        $aggregate->expects(self::any())
            ->method('getId')
            ->willReturn($aggregateId);

        $eventStore->expects(self::once())
            ->method('getEventsForAggregate')
            ->with($aggregateId)
            ->willReturn($events);

        $eventBus->expects(self::exactly($events->size()))
            ->method('publish');

        $eventPlayer = new AggregateEventPlayer($eventBus, $eventStore);
        $eventPlayer->replayEventsByAggregate($aggregate);
    }

    /**
     * @test
     */
    public function replayEventsByAggregateIdReplaysEventsByAggregateId()
    {
        $serializer = $this->getSerializer();
        $eventClassMap = $this->getEventClassMap();
        $eventStorage = $this->getEventStorage();
        $eventBus = $this->getEventBus();
        $eventStore = $this->getEventStore($eventBus, $eventStorage, $serializer, $eventClassMap);

        $aggregateId = Identity::createNew();

        $basicEvents = [
            new AggregateEventPlayerTest_Event(),
            new AggregateEventPlayerTest_Event()
        ];

        $events = new Events($basicEvents);

        $eventDescriptors = [
            EventDescriptor::record(
                $aggregateId->getValue(),
                'aggregateType',
                $basicEvents[0]->getEventName(),
                $serializer->serialize($basicEvents[0]),
                1
            ),
            EventDescriptor::record(
                $aggregateId->getValue(),
                'aggregateType',
                $basicEvents[0]->getEventName(),
                $serializer->serialize($basicEvents[0]),
                2
            )
        ];

        $eventStore->expects(self::any())
            ->method('getEventsForAggregate')
            ->with($aggregateId)
            ->willReturn($events);

        $eventBus->expects(self::exactly($events->size()))
            ->method('publish');

        $eventPlayer = new AggregateEventPlayer($eventBus, $eventStore);
        $eventPlayer->replayEventsByAggregateId($aggregateId);
    }
}

class AggregateEventPlayerTest_Event extends Event
{
}