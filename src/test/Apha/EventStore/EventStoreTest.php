<?php

namespace Apha\EventStore;

use Apha\Domain\Message\Events;
use Apha\EventStore\Storage\EventStorage;
use Apha\MessageBus\EventBus;
use Apha\Domain\Message\Event;
use JMS\Serializer\SerializerInterface;
use Ramsey\Uuid\Uuid;

class EventStoreTest extends \PHPUnit_Framework_TestCase
{
    private function getEventBus()
    {
        return $this->getMockBuilder(EventBus::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getEventStorage()
    {
        return $this->getMockBuilder(EventStorage::class)
            ->getMockForAbstractClass();
    }

    public function getSerializer()
    {
        return $this->getMockBuilder(SerializerInterface::class)
            ->getMockForAbstractClass();
    }

    public function getEventMap()
    {
        return $this->getMockBuilder(EventClassMap::class)
            ->disableOriginalConstructor()
            ->setMethods(['getClassByEventName'])
            ->getMock();
    }

    public function testSaveEventsForAggregateAppendsEventsToStorageForNewAggregate()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Uuid::uuid4();

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
        $eventStore->save($aggregateId, $events, -1);
    }

    /**
     * @expectedException \Apha\EventStore\ConcurrencyException
     */
    public function testSaveEventsWithDifferentPlayHeadThrowsException()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Uuid::uuid4();

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
        $eventStore->save($aggregateId, $events, 0);
    }

    public function testSaveEventsForAggregateAppendsEventsToStorageForExistingAggregate()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Uuid::uuid4();

        $events = new Events([
            new EventStoreTest_Event1(),
            new EventStoreTest_Event2()
        ]);

        $descriptors = [
            EventDescriptor::reconstructFromArray([
                'identity' => $aggregateId->toString(),
                'event' => EventStoreTest_Event1::getName(),
                'payload' => '{}',
                'playHead' => 1,
                'recorded' => date('r')
            ])
        ];

        $storage->expects(self::once())
            ->method('find')
            ->with($aggregateId->toString())
            ->willReturn($descriptors);

        $storage->expects(self::exactly($events->size()))
            ->method('append');

        $serializer->expects(self::exactly($events->size()))
            ->method('serialize')
            ->willReturn('{}');

        $eventStore = new EventStore($eventBus, $storage, $serializer, $eventMap);
        $eventStore->save($aggregateId, $events, 1);
    }

    public function testGetEventsForAggregateRetrievesEventLog()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Uuid::uuid4();

        $descriptors = [
            EventDescriptor::reconstructFromArray([
                'identity' => $aggregateId->toString(),
                'event' => EventStoreTest_Event1::getName(),
                'payload' => '{}',
                'playHead' => 1,
                'recorded' => date('r')
            ]),
            EventDescriptor::reconstructFromArray([
                'identity' => $aggregateId->toString(),
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
            ->with($aggregateId->toString())
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
     * @expectedException \Apha\EventStore\AggregateNotFoundException
     */
    public function testGetEventsForAggregateThrowsExceptionIfAggregateNotFound()
    {
        $eventBus = $this->getEventBus();
        $storage = $this->getEventStorage();
        $serializer = $this->getSerializer();
        $eventMap = $this->getEventMap();

        $aggregateId = Uuid::uuid4();

        $eventStore = new EventStore($eventBus, $storage, $serializer, $eventMap);
        $eventStore->getEventsForAggregate($aggregateId);
    }

    public function testGetAggregateIdsRetrievesIdentitiesFromStorage()
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

class EventStoreTest_Event1 extends Event {}
class EventStoreTest_Event2 extends Event {}