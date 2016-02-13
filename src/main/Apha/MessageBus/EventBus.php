<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Event;

abstract class EventBus
{
    /**
     * @param Event $event
     * @return void
     */
    abstract public function publish(Event $event);
}