<?php
declare(strict_types=1);

namespace Apha\MessageHandler;

use Apha\Domain\Message\Event;

interface EventHandler
{
    /**
     * @param Event $event
     * @return void
     */
    public function on(Event $event);
}