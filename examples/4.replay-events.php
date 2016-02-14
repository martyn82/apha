<?php
declare(strict_types = 1);
require_once __DIR__ . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);

use JMS\Serializer\Annotation as Serializer;

class DemoDocument implements \Apha\ReadStore\Document
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
        $this->demonstrated = true;
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
     * @var Apha\ReadStore\Storage\ReadStorage
     */
    private $readStorage;

    /**
     * @param \Apha\ReadStore\Storage\ReadStorage $readStorage
     */
    public function __construct(\Apha\ReadStore\Storage\ReadStorage $readStorage)
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
        } catch (\Apha\ReadStore\Storage\DocumentNotFoundException $e) {
            $document = new DemoDocument($event->getId()->getValue(), $event->getVersion());
        }

        $document->apply($event);
        $this->readStorage->upsert($event->getId()->getValue(), $document);

        var_dump($this->readStorage->find($event->getId()->getValue()));
    }
}

$eventStorage = new \Apha\EventStore\Storage\MemoryEventStorage();
$readStore = new \Apha\ReadStore\Storage\MemoryReadStorage();

$eventBus = new \Apha\MessageBus\SimpleEventBus([
    DemonstratedEvent::class => [new DemonstratedEventHandler($readStore)]
]);

$serializer = \JMS\Serializer\SerializerBuilder::create()->build();

$eventStore = new \Apha\EventStore\EventStore(
    $eventBus,
    $eventStorage,
    $serializer,
    new \Apha\EventStore\EventClassMap([
        DemonstratedEvent::class
    ])
);

$identity = \Apha\Domain\Identity::createNew();
$event = new DemonstratedEvent($identity);
$event->setVersion(0);

$eventStorage->append(
    \Apha\EventStore\EventDescriptor::record(
        $identity->getValue(),
        $event->getEventName(),
        $serializer->serialize($event, 'json'),
        $event->getVersion()
    )
);

$events = $eventStore->getEventsForAggregate($identity);
foreach ($events->getIterator() as $event) {
    $eventBus->publish($event);
}