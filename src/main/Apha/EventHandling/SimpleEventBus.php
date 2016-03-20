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
     * @param string $eventClass
     * @param EventHandler $handler
     */
    public function removeHandler(string $eventClass, EventHandler $handler)
    {
        if (!array_key_exists($eventClass, $this->eventHandlerMap)) {
            return;
        }

        $removeIndex = -1;
        foreach ($this->eventHandlerMap[$eventClass] as $index => $registeredHandler) {
            if ($handler == $registeredHandler) {
                $removeIndex = $index;
                break;
            }
        }

        unset($this->eventHandlerMap[$eventClass][$removeIndex]);
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function publish(Event $event): bool
    {
        $handled = false;

        if (array_key_exists(Event::class, $this->eventHandlerMap)) {
            foreach ($this->eventHandlerMap[Event::class] as $handler) {
                /* @var $handler EventHandler */
                $handler->on($event);
                $handled = true;
            }
        }

        $eventClassName = get_class($event);

        if (!array_key_exists($eventClassName, $this->eventHandlerMap)) {
            return false;
        }

        foreach ($this->eventHandlerMap[$eventClassName] as $handler) {
            /* @var $handler EventHandler */
            $handler->on($event);
            $handled = true;
        }

        return $handled;
    }
}