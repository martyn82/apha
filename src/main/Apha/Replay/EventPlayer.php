<?php
declare(strict_types = 1);

namespace Apha\Replay;

use Apha\EventHandling\EventBus;
use Apha\Message\Events;

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
        /* @var $event \Apha\Message\Event */
        foreach ($events->getIterator() as $event) {
            $this->eventBus->publish($event);
        }
    }
}