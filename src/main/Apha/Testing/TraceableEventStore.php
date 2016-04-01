<?php
declare(strict_types = 1);

namespace Apha\Testing;

use Apha\EventHandling\EventBus;
use Apha\EventStore\EventClassMap;
use Apha\EventStore\EventStore;
use Apha\EventStore\Storage\EventStorage;
use Apha\Serializer\Serializer;

class TraceableEventStore extends EventStore
{
    /**
     * @var TraceableEventBus
     */
    private $eventBus;

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
        $this->eventBus = new TraceableEventBus($eventBus);
        parent::__construct($this->eventBus, $storage, $serializer, $eventMap);
    }

    /**
     * @return TraceableEventBus
     */
    public function getEventBus(): TraceableEventBus
    {
        return $this->eventBus;
    }
}
