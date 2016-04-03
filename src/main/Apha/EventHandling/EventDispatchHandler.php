<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;

interface EventDispatchHandler
{
    /**
     * @param Event $command
     * @return void
     */
    public function onBeforeDispatch(Event $command);

    /**
     * @param Event $command
     * @return void
     */
    public function onDispatchSuccessful(Event $command);

    /**
     * @param Event $command
     * @return void
     */
    public function onDispatchFailed(Event $command);
}
