<?php
declare(strict_types = 1);
require_once __DIR__ . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);

use JMS\Serializer\Annotation as Serializer;

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

class DemoDocument implements \Apha\StateStore\Document
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $version;

    /**
     * @var bool
     */
    private $demonstrated = false;

    /**
     * @param string $id
     * @param int $version
     */
    public function __construct(string $id, int $version)
    {
        $this->id = $id;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion() : int
    {
        return $this->version;
    }

    /**
     * @param \Apha\Message\Event $event
     * @return void
     */
    public function apply(\Apha\Message\Event $event)
    {
        $this->version = $event->getVersion();

        if ($event instanceof DemonstratedEvent) {
            $this->demonstrated = true;
        }
    }
}

final class CreatedEvent extends \Apha\Message\Event
{
    /**
     * @Serializer\Type("Apha\Domain\Identity")
     * @var \Apha\Domain\Identity
     */
    private $id;

    /**
     * @param \Apha\Domain\Identity $id
     */
    public function __construct(\Apha\Domain\Identity $id)
    {
        $this->id = $id;
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getId() : \Apha\Domain\Identity
    {
        return $this->id;
    }
}

final class DemonstratedEvent extends \Apha\Message\Event
{
    /**
     * @Serializer\Type("Apha\Domain\Identity")
     * @var \Apha\Domain\Identity
     */
    private $id;

    /**
     * @param \Apha\Domain\Identity $id
     */
    public function __construct(\Apha\Domain\Identity $id)
    {
        $this->id = $id;
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getId() : \Apha\Domain\Identity
    {
        return $this->id;
    }
}

class DemonstratedEventHandler implements \Apha\MessageHandler\EventHandler
{
    /**
     * @var Apha\StateStore\Storage\StateStorage
     */
    private $readStorage;

    /**
     * @param \Apha\StateStore\Storage\StateStorage $readStorage
     */
    public function __construct(\Apha\StateStore\Storage\StateStorage $readStorage)
    {
        $this->readStorage = $readStorage;
    }

    /**
     * @param \Apha\Message\Event $event
     */
    public function on(\Apha\Message\Event $event)
    {
        try {
            $document = $this->readStorage->find($event->getId()->getValue());
        } catch (\Apha\StateStore\Storage\DocumentNotFoundException $e) {
            $document = new DemoDocument($event->getId()->getValue(), $event->getVersion());
        }

        $document->apply($event);
        $this->readStorage->upsert($event->getId()->getValue(), $document);

        echo "Stored state version: {$event->getVersion()}\n";
    }
}

// A logger
$logger = new \Monolog\Logger('default');

// A serializer
$serializer = new \Apha\Serializer\JsonSerializer();

// An event storage
$eventStorage = new \Apha\EventStore\Storage\MemoryEventStorage();

// A state storage
$stateStorage = new \Apha\StateStore\Storage\MemoryStateStorage();

// An event bus with an event handler bound to the DemonstratedEvent
$eventBus = new \Apha\EventHandling\LoggingEventBus(
    new \Apha\EventHandling\SimpleEventBus([
        CreatedEvent::class => [new DemonstratedEventHandler($stateStorage)],
        DemonstratedEvent::class => [new DemonstratedEventHandler($stateStorage)]
    ]),
    $logger
);

// The event store
$eventStore = new \Apha\EventStore\EventStore(
    $eventBus,
    $eventStorage,
    $serializer,
    new \Apha\EventStore\EventClassMap([
        CreatedEvent::class,
        DemonstratedEvent::class
    ])
);

// Build a series of events for an aggregate to mock initial state.
// This is not the preferred way, but used for simplicity! You should use commands to achieve this.
$identity = \Apha\Domain\Identity::createNew();
$event = new CreatedEvent($identity);
$event->setVersion(1);

$eventStorage->append(
    \Apha\EventStore\EventDescriptor::record(
        $identity->getValue(),
        $event->getEventName(),
        $serializer->serialize($event),
        $event->getVersion()
    )
);

$event = new DemonstratedEvent($identity);
$event->setVersion(2);

$eventStorage->append(
    \Apha\EventStore\EventDescriptor::record(
        $identity->getValue(),
        $event->getEventName(),
        $serializer->serialize($event),
        $event->getVersion()
    )
);

// Use the EventPlayer to replay the events for an Aggregate.
$eventPlayer = new \Apha\Replay\AggregateEventPlayer($eventBus, $eventStore);
$eventPlayer->replayEventsByAggregateId($identity);

// Inspect the current aggregate state
var_dump(
    $stateStorage->find($identity->getValue())
);