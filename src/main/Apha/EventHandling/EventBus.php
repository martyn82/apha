<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;

abstract class EventBus
{
    /**
     * @param string $eventClass
     * @param EventHandler $handler
     * @return void
     */
    abstract public function addHandler(string $eventClass, EventHandler $handler);

    /**
     * @param Event $event
     * @return bool
     */
    abstract public function publish(Event $event): bool;
}