<?php
declare(strict_types=1);

namespace Apha\EventStore;

use Apha\Domain\Message\{Event, Events};
use Apha\EventStore\Storage\EventStorage;
use Apha\MessageBus\EventBus;
use JMS\Serializer\SerializerInterface;
use Ramsey\Uuid\{Uuid, UuidInterface};

class EventStore
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var EventStorage
     */
    private $storage;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EventClassMap
     */
    private $eventMap;

    /**
     * @var array
     */
    private $current;

    /**
     * @param EventBus $eventBus
     * @param EventStorage $storage
     * @param SerializerInterface $serializer
     * @param EventClassMap $eventMap
     */
    public function __construct(
        EventBus $eventBus,
        EventStorage $storage,
        SerializerInterface $serializer,
        EventClassMap $eventMap
    ) {
        $this->eventBus = $eventBus;
        $this->storage = $storage;
        $this->serializer = $serializer;
        $this->eventMap = $eventMap;
        $this->current = [];
    }

    /**
     * @param UuidInterface $aggregateId
     * @param Events $events
     * @param int $expectedPlayHead
     * @throws ConcurrencyException
     */
    public function save(UuidInterface $aggregateId, Events $events, int $expectedPlayHead)
    {
        if (!$this->isValidPlayHead($aggregateId, $expectedPlayHead)) {
            throw new ConcurrencyException($expectedPlayHead, -1);
        }

        $playHead = $expectedPlayHead;

        foreach ($events->getIterator() as $event) {
            /* @var $event Event */
            $playHead++;
            $event->setVersion($playHead);

            $this->saveEvent($aggregateId, $event);
            $this->eventBus->publish($event);
        }
    }

    /**
     * @param UuidInterface $aggregateId
     * @param int $playHead
     * @return bool
     */
    private function isValidPlayHead(UuidInterface $aggregateId, int $playHead) : bool
    {
        $descriptors = $this->storage->find($aggregateId->toString());

        if (!empty($descriptors)) {
            $this->current[$aggregateId->toString()] = end($descriptors)->getPlayHead();
        } else {
            $this->current[$aggregateId->toString()] = -1;
        }

        if ($playHead != -1 && $this->current[$aggregateId->toString()] != $playHead) {
            return false;
        }

        return true;
    }

    /**
     * @param UuidInterface $aggregateId
     * @param Event $event
     */
    private function saveEvent(UuidInterface $aggregateId, Event $event)
    {
        $eventData = EventDescriptor::record(
            $aggregateId->toString(),
            $event->getEventName(),
            $this->serializer->serialize($event, 'json'),
            $event->getVersion()
        );

        $this->storage->append($eventData);
    }

    /**
     * @param UuidInterface $aggregateId
     * @return Events
     * @throws AggregateNotFoundException
     */
    public function getEventsForAggregate(UuidInterface $aggregateId) : Events
    {
        if (!$this->storage->contains($aggregateId->toString())) {
            throw new AggregateNotFoundException($aggregateId);
        }

        $eventData = $this->storage->find($aggregateId->toString());

        $events = array_map(
            function (EventDescriptor $data) {
                return $this->serializer->deserialize(
                    $data->getPayload(),
                    $this->eventMap->getClassByEventName($data->getEvent()),
                    'json'
                );
            },
            $eventData
        );

        return new Events($events);
    }

    /**
     * @return UuidInterface[]
     */
    public function getAggregateIds() : array
    {
        return array_map(
            function (string $identity) {
                return Uuid::fromString($identity);
            },
            $this->storage->findIdentities()
        );
    }
}