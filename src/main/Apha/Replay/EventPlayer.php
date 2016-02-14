<?php
declare(strict_types = 1);

namespace Apha\Replay;

use Apha\Message\Events;
use Apha\MessageBus\EventBus;

class EventPlayer
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @param EventBus $eventBus
     */
    public function __construct(EventBus $eventBus)
    {
        $this->eventBus = $eventBus;
    }

    /**
     * @param Events $events
     */
    public function play(Events $events)
    {
        foreach ($events->getIterator() as $event) {
            /* @var $event \Apha\Message\Event */
            $this->eventBus->publish($event);
        }
    }
}