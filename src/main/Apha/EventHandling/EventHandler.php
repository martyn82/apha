<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;

interface EventHandler
{
    /**
     * @param Event $event
     * @return void
     */
    public function on(Event $event);
}