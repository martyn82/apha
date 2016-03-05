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

    /**
     * @test
     */
    public function playToVersionPublishesEventsUpToAVersion()
    {
        $eventBus = $this->getMockBuilder(EventBus::class)
            ->getMockForAbstractClass();

        $eventVersion1 = new EventPlayerTest_Event();
        $eventVersion1->setVersion(1);

        $eventVersion2 = new EventPlayerTest_Event();
        $eventVersion2->setVersion(2);

        $events = new Events([
            $eventVersion1,
            $eventVersion2
        ]);

        $eventBus->expects(self::once())
            ->method('publish');

        $eventPlayer = new EventPlayer($eventBus);
        $eventPlayer->playToVersion($events, 1);
    }
}

class EventPlayerTest_Event extends Event
{
}