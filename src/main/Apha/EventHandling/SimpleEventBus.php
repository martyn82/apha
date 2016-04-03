<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;
use Apha\Serializer\ArrayConverter;
use Psr\Log\LoggerInterface;

class SimpleEventBus extends EventBus
{
    /**
     * @var array
     */
    private $eventHandlerMap;

    /**
     * @var EventLogger
     */
    private $logger;

    /**
     * @param array $eventHandlerMap
     */
    public function __construct(array $eventHandlerMap = [])
    {
        $this->eventHandlerMap = $eventHandlerMap;
    }

    /**
     * @param EventLogger $logger
     */
    public function setLogger(EventLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Event $event
     */
    private function onBeforeDispatch(Event $event)
    {
        if ($this->logger == null) {
            return;
        }
     
        $this->logger->onBeforeDispatch($event);
    }

    /**
     * @param Event $event
     */
    private function onDispatchSuccessful(Event $event)
    {
        if ($this->logger == null) {
            return;
        }
        
        $this->logger->onDispatchSuccessful($event);
    }

    /**
     * @param Event $event
     */
    private function onDispatchFailed(Event $event)
    {
        if ($this->logger == null) {
            return;
        }

        $this->logger->onDispatchFailed($event);
    }

    /**
     * @param Event $event
     * @param bool $handled
     */
    private function onAfterDispatch(Event $event, bool $handled)
    {
        if ($handled) {
            $this->onDispatchSuccessful($event);
        } else {
            $this->onDispatchFailed($event);
        }
    }

    /**
     * @param string $eventClass
     * @param EventHandler $handler
     */
    public function addHandler(string $eventClass, EventHandler $handler)
    {
        if (!array_key_exists($eventClass, $this->eventHandlerMap)) {
            $this->eventHandlerMap[$eventClass] = [];
        }

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
        $this->onBeforeDispatch($event);
        
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
            $this->onAfterDispatch($event, $handled);
            return $handled;
        }

        foreach ($this->eventHandlerMap[$eventClassName] as $handler) {
            /* @var $handler EventHandler */
            $handler->on($event);
            $handled = true;
        }

        $this->onAfterDispatch($event, $handled);
        return $handled;
    }
}
