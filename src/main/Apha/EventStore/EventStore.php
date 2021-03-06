<?php
declare(strict_types = 1);

namespace Apha\EventStore;

use Apha\Message\Event;
use Apha\Message\Events;
use Apha\Domain\Identity;
use Apha\EventHandling\EventBus;
use Apha\EventStore\Storage\EventStorage;
use Apha\Serializer\Serializer;

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
     * @var Serializer
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
     * @param Serializer $serializer
     * @param EventClassMap $eventMap
     */
    public function __construct(
        EventBus $eventBus,
        EventStorage $storage,
        Serializer $serializer,
        EventClassMap $eventMap
    )
    {
        $this->eventBus = $eventBus;
        $this->storage = $storage;
        $this->serializer = $serializer;
        $this->eventMap = $eventMap;
        $this->current = [];
    }

    /**
     * @param Identity $aggregateId
     * @param string $aggregateType
     * @param Events $events
     * @param int $expectedPlayHead
     * @throws ConcurrencyException
     */
    public function save(Identity $aggregateId, string $aggregateType, Events $events, int $expectedPlayHead)
    {
        if (!$this->isValidPlayHead($aggregateId, $expectedPlayHead)) {
            throw new ConcurrencyException($expectedPlayHead, $this->current[$aggregateId->getValue()]);
        }

        $playHead = $expectedPlayHead;

        foreach ($events->getIterator() as $event) {
            /* @var $event Event */
            $playHead++;
            $event->setVersion($playHead);

            $this->saveEvent($aggregateId, $aggregateType, $event);
            $this->eventBus->publish($event);
        }
    }

    /**
     * @param Identity $aggregateId
     * @param int $playHead
     * @return bool
     */
    private function isValidPlayHead(Identity $aggregateId, int $playHead): bool
    {
        $descriptors = $this->storage->find($aggregateId->getValue());

        if (!empty($descriptors)) {
            $this->current[$aggregateId->getValue()] = end($descriptors)->getPlayHead();
        } else {
            $this->current[$aggregateId->getValue()] = -1;
        }

        if ($playHead != -1 && $this->current[$aggregateId->getValue()] != $playHead) {
            return false;
        }

        return true;
    }

    /**
     * @param Identity $aggregateId
     * @param string $aggregateType
     * @param Event $event
     */
    private function saveEvent(Identity $aggregateId, string $aggregateType, Event $event)
    {
        $eventData = EventDescriptor::record(
            $aggregateId->getValue(),
            $aggregateType,
            $event->getEventName(),
            $this->serializer->serialize($event),
            $event->getVersion()
        );

        $this->storage->append($eventData);
    }

    /**
     * @param Identity $aggregateId
     * @return Events
     * @throws AggregateNotFoundException
     */
    public function getEventsForAggregate(Identity $aggregateId): Events
    {
        if (!$this->storage->contains($aggregateId->getValue())) {
            throw new AggregateNotFoundException($aggregateId);
        }

        $eventData = $this->storage->find($aggregateId->getValue());

        $events = array_map(
            function (EventDescriptor $data): Event {
                return $this->serializer->deserialize(
                    $data->getPayload(),
                    $this->eventMap->getClassByEventName($data->getEvent())
                );
            },
            $eventData
        );

        return new Events($events);
    }

    /**
     * @return Identity[]
     */
    public function getAggregateIds(): array
    {
        return array_map(
            function (string $identity): Identity {
                return Identity::fromString($identity);
            },
            $this->storage->findIdentities()
        );
    }
}