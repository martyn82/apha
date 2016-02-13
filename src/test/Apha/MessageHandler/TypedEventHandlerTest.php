<?php
declare(strict_types=1);

namespace Apha\MessageHandler;

use Apha\Domain\Message\Event;

class TypedEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithTypeInflection()
    {
        $handler = $this->getMockBuilder(TypedEventHandler::class)
            ->setMethods(['onSomeEvent'])
            ->getMockForTrait();

        $event = $this->getMockBuilder(Event::class)
            ->setMockClassName('SomeEvent')
            ->setMethods(['getEventName'])
            ->getMock();

        $event->expects(self::any())
            ->method('getEventName')
            ->willReturn('SomeEvent');

        $handler->expects(self::once())
            ->method('onSomeEvent')
            ->with($event);

        $handler->on($event);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHandleThrowsExceptionForUnknownEvent()
    {
        $handler = $this->getMockBuilder(TypedEventHandler::class)
            ->getMockForTrait();

        $event = $this->getMockBuilder(Event::class)
            ->setMockClassName('SomeEvent')
            ->setMethods(['getEventName'])
            ->getMock();

        $event->expects(self::any())
            ->method('getEventName')
            ->willReturn('SomeEvent');

        $handler->on($event);
    }
}