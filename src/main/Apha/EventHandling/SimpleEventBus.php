<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;

class SimpleEventBus extends EventBus
{
    /**
     * @var array
     */
    private $eventHandlerMap;

    /**
     * @param array $eventHandlerMap
     */
    public function __construct(array $eventHandlerMap)
    {
        $this->eventHandlerMap = $eventHandlerMap;
    }

    /**
     * @param string $eventClass
     * @param EventHandler $handler
     */
    public function addHandler(string $eventClass, EventHandler $handler)
    {
        $this->eventHandlerMap[$eventClass][] = $handler;
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function publish(Event $event): bool
    {
        $eventClassName = get_class($event);

        if (!array_key_exists($eventClassName, $this->eventHandlerMap)) {
            return false;
        }

        $handled = false;

        foreach ($this->eventHandlerMap[$eventClassName] as $handler) {
            /* @var $handler EventHandler */
            $handler->on($event);
            $handled = true;
        }

        return $handled;
    }
}