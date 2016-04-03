<?php
declare(strict_types = 1);

namespace Apha\Testing;

use Apha\EventHandling\EventBus;
use Apha\EventHandling\EventHandler;
use Apha\Message\Event;

class TraceableEventBus extends EventBus implements TraceableEventHandler
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var array
     */
    private $eventLog;
    
    /**
     * @param EventBus $eventBus
     */
    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
        $this->eventLog = [];
    }

    /**
     * @param string $eventClass
     * @param EventHandler $handler
     * @return void
     */
    public function addHandler(string $eventClass, EventHandler $handler)
    {
        $this->eventBus->addHandler($eventClass, $handler);
    }

    /**
     * @param string $eventClass
     * @param EventHandler $handler
     * @return void
     */
    public function removeHandler(string $eventClass, EventHandler $handler)
    {
        $this->eventBus->removeHandler($eventClass, $handler);
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function publish(Event $event): bool
    {
        $this->eventLog[] = $event;
        return true;
    }

    /**
     */
    public function clearTraceLog()
    {
        $this->eventLog = [];
    }

    /**
     * @return array
     */
    public function getEvents(): array
    {
        return $this->eventLog;
    }
}
