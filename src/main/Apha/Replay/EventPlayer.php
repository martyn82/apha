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
        $this->playToVersion($events, PHP_INT_MAX);
    }

    /**
     * @param Events $events
     * @param int $version
     */
    public function playToVersion(Events $events, int $version)
    {
        foreach ($events->getIterator() as $event) {
            /* @var $event \Apha\Message\Event */

            if ($event->getVersion() > $version) {
                return;
            }

            $this->eventBus->publish($event);
        }
    }
}