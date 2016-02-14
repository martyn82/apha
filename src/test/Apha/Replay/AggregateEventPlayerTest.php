<?php
declare(strict_types = 1);

namespace Apha\Replay;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;
use Apha\EventStore\EventClassMap;
use Apha\EventStore\EventDescriptor;
use Apha\EventStore\EventStore;
use Apha\EventStore\Storage\EventStorage;
use Apha\Message\Event;
use Apha\Message\Events;
use Apha\MessageBus\EventBus;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializerBuilder;

class AggregateEventPlayerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return SerializerInterface
     */
    private function getSerializer()
    {
        return SerializerBuilder::create()->build();
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
     * @param SerializerInterface $serializer
     * @param EventClassMap $eventClassMap
     * @return EventStore
     */
    private function getEventStore(
        EventBus $eventBus,
        EventStorage $eventStorage,
        SerializerInterface $serializer,
        EventClassMap $eventClassMap
    )
    {
        return $this->getMockBuilder(EventStore::class)
            ->setConstructorArgs([$eventBus, $eventStorage, $serializer, $eventClassMap])
            ->getMockForAbstractClass();
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
                $basicEvents[0]->getEventName(),
                $serializer->serialize($basicEvents[0], 'json'),
                1
            ),
            EventDescriptor::record(
                $aggregateId->getValue(),
                $basicEvents[0]->getEventName(),
                $serializer->serialize($basicEvents[0], 'json'),
                2
            )
        ];

        $aggregate->expects(self::any())
            ->method('getId')
            ->willReturn($aggregateId);

        $eventStore->expects(self::any())
            ->method('getEventsForAggregate')
            ->with($aggregateId)
            ->willReturn($events);

        $eventStorage->expects(self::any())
            ->method('contains')
            ->with($aggregateId)
            ->willReturn(true);

        $eventStorage->expects(self::once())
            ->method('find')
            ->with($aggregateId)
            ->willReturn($eventDescriptors);

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
                $basicEvents[0]->getEventName(),
                $serializer->serialize($basicEvents[0], 'json'),
                1
            ),
            EventDescriptor::record(
                $aggregateId->getValue(),
                $basicEvents[0]->getEventName(),
                $serializer->serialize($basicEvents[0], 'json'),
                2
            )
        ];

        $eventStore->expects(self::any())
            ->method('getEventsForAggregate')
            ->with($aggregateId)
            ->willReturn($events);

        $eventStorage->expects(self::any())
            ->method('contains')
            ->with($aggregateId)
            ->willReturn(true);

        $eventStorage->expects(self::once())
            ->method('find')
            ->with($aggregateId)
            ->willReturn($eventDescriptors);

        $eventBus->expects(self::exactly($events->size()))
            ->method('publish');

        $eventPlayer = new AggregateEventPlayer($eventBus, $eventStore);
        $eventPlayer->replayEventsByAggregateId($aggregateId);
    }
}

class AggregateEventPlayerTest_Event extends Event
{
}