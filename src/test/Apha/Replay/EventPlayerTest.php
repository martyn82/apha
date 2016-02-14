<?php
declare(strict_types = 1);

namespace Apha\Replay;

use Apha\Message\Event;
use Apha\Message\Events;
use Apha\MessageBus\EventBus;

class EventPlayerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function playPublishesEachEvent()
    {
        $eventBus = $this->getMockBuilder(EventBus::class)
            ->getMockForAbstractClass();

        $events = new Events([
            new EventPlayerTest_Event(),
            new EventPlayerTest_Event()
        ]);

        $eventBus->expects(self::exactly($events->size()))
            ->method('publish');

        $eventPlayer = new EventPlayer($eventBus);
        $eventPlayer->play($events);
    }
}

class EventPlayerTest_Event extends Event
{
}