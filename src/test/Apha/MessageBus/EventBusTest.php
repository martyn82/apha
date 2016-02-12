<?php

namespace Apha\MessageBus;

use Apha\Domain\Message\Event;
use Apha\MessageHandler\EventHandler;

class EventBusTest extends \PHPUnit_Framework_TestCase
{
    public function testPublishPropagatesEventToRegisteredHandlers()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $handler1 = $this->getMockBuilder(EventHandler::class)
            ->getMock();

        $handler2 = $this->getMockBuilder(EventHandler::class)
            ->getMock();

        $handler1->expects(self::once())
            ->method('on')
            ->with($event);

        $handler2->expects(self::once())
            ->method('on')
            ->with($event);

        $eventBus = new EventBus([
            get_class($event) => [$handler1, $handler2]
        ]);

        $eventBus->publish($event);
    }

    public function testPublishEventWithoutHandlerPassesSilently()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $eventBus = new EventBus([]);
        $eventBus->publish($event);
    }
}