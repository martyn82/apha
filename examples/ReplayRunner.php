#!/usr/bin/env php
<?php
declare(strict_types = 1);

namespace Apha\Examples;

use Apha\Domain\Identity;
use Apha\EventHandling\EventLogger;
use Apha\EventHandling\SimpleEventBus;
use Apha\EventStore\EventClassMap;
use Apha\EventStore\EventDescriptor;
use Apha\EventStore\EventStore;
use Apha\EventStore\Storage\EventStorage;
use Apha\EventStore\Storage\MemoryEventStorage;
use Apha\Examples\Domain\Demo\CreatedEvent;
use Apha\Examples\Domain\Demo\DemonstratedEvent;
use Apha\Examples\Domain\Demo\DemonstratedEventHandler;
use Apha\Replay\AggregateEventPlayer;
use Apha\Serializer\ArrayConverter;
use Apha\Serializer\JsonSerializer;
use Apha\Serializer\Serializer;
use Apha\StateStore\Storage\MemoryStateStorage;
use Monolog\Logger;

require_once __DIR__ . "/Runner.php";

/**
 * This example demonstrates the use of an event player to reconstruct the state of an aggregate.
 *
 * In this example, two events are defined: CreatedEvent, occurs whenever an aggregate instance was created, and the
 * DemonstratedEvent to indicate the demonstration was done on the aggregate.
 * The resulting state is version 2 (created is version 1) of the aggregate where the "demonstrated" indication is set
 * to "true".
 *
 * The replay of events re-applies the previously recorded events on the aggregate, but will not record them again.
 * The event player publishes replayed events on its event bus, so be cautious what handlers you attach there. Usually,
 * this will be a different event bus instance than you would use for new events. You may not want to re-send e-mails
 * for instance.
 */
class ReplayRunner extends Runner
{
    /**
     * @return void
     */
    public function run()
    {
        // A logger
        $logger = new Logger('default');

        // A serializer
        $serializer = new JsonSerializer();

        // An event storage
        $eventStorage = new MemoryEventStorage();

        // A state storage
        $stateStorage = new MemoryStateStorage();

        // An event bus with an event handler bound to the DemonstratedEvent
        $eventBus = new SimpleEventBus([
            CreatedEvent::class => [new DemonstratedEventHandler($stateStorage)],
            DemonstratedEvent::class => [new DemonstratedEventHandler($stateStorage)]
        ]);
        $eventBus->setLogger(new EventLogger($logger));

        // The event store
        $eventStore = new EventStore(
            $eventBus,
            $eventStorage,
            $serializer,
            new EventClassMap([
                CreatedEvent::class,
                DemonstratedEvent::class
            ])
        );

        $identity = Identity::createNew();

        // Build a series of events for an aggregate to mock initial state.
        $this->prepareSomeEvents($identity, $eventStorage, $serializer);

        // Use the EventPlayer to replay the events for an Aggregate.
        $eventPlayer = new AggregateEventPlayer($eventBus, $eventStore);
        $eventPlayer->replayEventsByAggregateId($identity);

        // Inspect the current aggregate state
        $logger->debug(
            'Current aggregate state', [
                'aggregate' => (new ArrayConverter())->objectToArray($stateStorage->find($identity->getValue()))
            ]
        );
    }

    /**
     * @param Identity $identity
     * @param EventStorage $eventStorage
     * @param Serializer $serializer
     */
    private function prepareSomeEvents(Identity $identity, EventStorage $eventStorage, Serializer $serializer)
    {
        // This is not the preferred way, but used for simplicity! You should use commands to achieve this.
        $event = new CreatedEvent($identity);
        $event->setVersion(1);

        $eventStorage->append(
            EventDescriptor::record(
                $identity->getValue(),
                'aggregateType',
                $event->getEventName(),
                $serializer->serialize($event),
                $event->getVersion()
            )
        );

        $event = new DemonstratedEvent($identity);
        $event->setVersion(2);

        $eventStorage->append(
            EventDescriptor::record(
                $identity->getValue(),
                'aggregateType',
                $event->getEventName(),
                $serializer->serialize($event),
                $event->getVersion()
            )
        );
    }
}

exit(ReplayRunner::main());
