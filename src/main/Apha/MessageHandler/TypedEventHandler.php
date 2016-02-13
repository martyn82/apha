<?php
declare(strict_types=1);

namespace Apha\MessageHandler;

use Apha\Domain\Message\Event;

trait TypedEventHandler
{
    /**
     * @param Event $event
     * @throws \InvalidArgumentException
     */
    public function on(Event $event)
    {
        $this->handleByInflection($event);
    }

    /**
     * @param Event $event
     * @throws \InvalidArgumentException
     */
    private function handleByInflection(Event $event)
    {
        $eventHandleMethod = 'on' . $event->getEventName();

        if (!method_exists($this, $eventHandleMethod)) {
            $eventClassName = get_class($event);
            throw new \InvalidArgumentException("Unable to handle event '{$eventClassName}'.");
        }

        $this->{$eventHandleMethod}($event);
    }
}