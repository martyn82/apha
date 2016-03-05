<?php
declare(strict_types = 1);

namespace Apha\EventStore;

use Apha\Message\{
    Event, Events
};
use Apha\Domain\Identity;
use Apha\EventHandling\EventBus;
use Apha\EventStore\Storage\EventStorage;
use Apha\Serializer\Serializer;

class EventStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EventBus
     */
    private function getEventBus()
    {
        return $this->getMockBuilder(EventBus::class)
            ->disableOriginalConstructor()
            ->getMock();
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
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->getMockBuilder(Serializer::class)
            ->getMockForAbstractClass();
    }

    /**
     * @return EventClassMap
     */
    public function getEventMap()
    {
        return $this->getMockBuilder(EventClassMap::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClassByEventName'])
            ->getMock();
    }

    /**
     * @test
     */
    public function saveEventsForAggregateAppendsEventsToStorageForNewAggregate()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Identity::createNew();

        $events = new Events([
            new EventStoreTest_Event1(),
            new EventStoreTest_Event2()
        ]);

        $storage->expects(self::exactly($events->size()))
            ->method('append');

        $serializer->expects(self::exactly($events->size()))
            ->method('serialize')
            ->willReturn('{}');

        $eventStore = new EventStore($eventBus, $storage, $serializer, $eventMap);
        $eventStore->save($aggregateId, 'type', $events, -1);
    }

    /**
     * @test
     * @expectedException \Apha\EventStore\ConcurrencyException
     */
    public function saveEventsWithDifferentPlayHeadThrowsException()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Identity::createNew();

        $events = new Events([
            new EventStoreTest_Event1(),
            new EventStoreTest_Event2()
        ]);

        $storage->expects(self::never())
            ->method('append');

        $serializer->expects(self::never())
            ->method('serialize')
            ->willReturn('{}');

        $eventStore = new EventStore($eventBus, $storage, $serializer, $eventMap);
        $eventStore->save($aggregateId, 'type', $events, 0);
    }

    /**
     * @test
     */
    public function saveEventsForAggregateAppendsEventsToStorageForExistingAggregate()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Identity::createNew();

        $events = new Events([
            new EventStoreTest_Event1(),
            new EventStoreTest_Event2()
        ]);

        $descriptors = [
            EventDescriptor::reconstructFromArray([
                'identity' => $aggregateId->getValue(),
                'type' => 'aggregateType',
                'event' => EventStoreTest_Event1::getName(),
                'payload' => '{}',
                'playHead' => 1,
                'recorded' => date('r')
            ])
        ];

        $storage->expects(self::once())
            ->method('find')
            ->with($aggregateId->getValue())
            ->willReturn($descriptors);

        $storage->expects(self::exactly($events->size()))
            ->method('append');

        $serializer->expects(self::exactly($events->size()))
            ->method('serialize')
            ->willReturn('{}');

        $eventStore = new EventStore($eventBus, $storage, $serializer, $eventMap);
        $eventStore->save($aggregateId, 'type', $events, 1);
    }

    /**
     * @test
     */
    public function getEventsForAggregateRetrievesEventLog()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Identity::createNew();

        $descriptors = [
            EventDescriptor::reconstructFromArray([
                'identity' => $aggregateId->getValue(),
                'type' => 'aggregateType',
                'event' => EventStoreTest_Event1::getName(),
                'payload' => '{}',
                'playHead' => 1,
                'recorded' => date('r')
            ]),
            EventDescriptor::reconstructFromArray([
                'identity' => $aggregateId->getValue(),
                'type' => 'aggregateType',
                'event' => EventStoreTest_Event2::getName(),
                'payload' => '{}',
                'playHead' => 2,
                'recorded' => date('r')
            ])
        ];

        $eventMap->expects(self::exactly(count($descriptors)))
            ->method('getClassByEventName')
            ->willReturnOnConsecutiveCalls(
                EventStoreTest_Event1::class,
                EventStoreTest_Event2::class
            );

        $storage->expects(self::once())
            ->method('contains')
            ->willReturn(true);

        $storage->expects(self::once())
            ->method('find')
            ->with($aggregateId->getValue())
            ->willReturn($descriptors);

        $serializer->expects(self::exactly(count($descriptors)))
            ->method('deserialize')
            ->willReturnOnConsecutiveCalls(
                new EventStoreTest_Event1(),
                new EventStoreTest_Event2()
            );

        $eventStore = new EventStore($eventBus, $storage, $serializer, $eventMap);
        $events = $eventStore->getEventsForAggregate($aggregateId);

        self::assertCount(count($descriptors), $events->getIterator());
    }

    /**
     * @test
     * @expectedException \Apha\EventStore\AggregateNotFoundException
     */
    public function getEventsForAggregateThrowsExceptionIfAggregateNotFound()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Identity::createNew();

        $eventStore = new EventStore($eventBus, $storage, $serializer, $eventMap);
        $eventStore->getEventsForAggregate($aggregateId);
    }

    /**
     * @test
     */
    public function getAggregateIdsRetrievesIdentitiesFromStorage()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $ids = [
            'fb1f37b1-e508-45c7-a564-89790aafcdd7',
            'ab1f37b1-e508-45c7-a564-89790aafcdd7'
        ];

        $storage->expects(self::once())
            ->method('findIdentities')
            ->willReturn($ids);

        $eventStore = new EventStore($eventBus, $storage, $serializer, $eventMap);
        $identities = $eventStore->getAggregateIds();

        self::assertCount(count($ids), $identities);
    }
}

class EventStoreTest_Event1 extends Event
{
}

class EventStoreTest_Event2 extends Event
{
}