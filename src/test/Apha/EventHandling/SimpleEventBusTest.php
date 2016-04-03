<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;

/**
 * @group annotations
 */
class SimpleEventBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function publishPropagatesEventToRegisteredHandlers()
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

        $eventBus = new SimpleEventBus([
            get_class($event) => [$handler1, $handler2]
        ]);

        $eventBus->publish($event);
    }

    /**
     * @test
     */
    public function publishEventWithoutHandlerPassesSilently()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $eventBus = new SimpleEventBus([]);
        $eventBus->publish($event);
    }

    /**
     * @test
     */
    public function handleAllEventsByRegisteringToBaseEvent()
    {
        $event = $this->getMockBuilder(SimpleEventBusTest_Event::class)
            ->getMock();

        $handler = $this->getMockBuilder(EventHandler::class)
            ->getMockForAbstractClass();

        $handler->expects(self::once())
            ->method('on')
            ->with($event);

        $eventBus = new SimpleEventBus([
            Event::class => [$handler]
        ]);
        $eventBus->publish($event);
    }

    /**
     * @test
     */
    public function addHandlerRegistersHandlerForEvent()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $handler = $this->getMockBuilder(EventHandler::class)
            ->getMockForAbstractClass();

        $handler->expects(self::once())
            ->method('on')
            ->with($event);

        $eventBus = new SimpleEventBus([]);
        $eventBus->addHandler(get_class($event), $handler);

        $eventBus->publish($event);
    }

    /**
     * @test
     */
    public function removeHandlerUnregistersHandlerForEvent()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $handler = $this->getMockBuilder(EventHandler::class)
            ->getMockForAbstractClass();

        $handler->expects(self::never())
            ->method('on')
            ->with($event);

        $eventBus = new SimpleEventBus([]);
        $eventBus->addHandler(get_class($event), $handler);
        $eventBus->removeHandler(get_class($event), $handler);

        $eventBus->publish($event);
    }

    /**
     * @test
     */
    public function removeUnknownHandlerIsIdempotent()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $handler = $this->getMockBuilder(EventHandler::class)
            ->getMockForAbstractClass();

        $handler->expects(self::never())
            ->method('on')
            ->with($event);

        $eventBus = new SimpleEventBus([]);
        $eventBus->removeHandler(get_class($event), $handler);

        $eventBus->publish($event);
    }

    /**
     * @test
     */
    public function callLoggerForBeforeDispatchAndSuccessfulDispatch()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $handler = $this->getMockBuilder(EventHandler::class)
            ->getMockForAbstractClass();

        $logger = $this->getMockBuilder(EventLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects(self::once())
            ->method('onBeforeDispatch')
            ->with($event);

        $logger->expects(self::once())
            ->method('onDispatchSuccessful')
            ->with($event);

        $eventBus = new SimpleEventBus([
            get_class($event) => [$handler]
        ]);

        $eventBus->setLogger($logger);
        $eventBus->publish($event);
    }

    /**
     * @test
     */
    public function callLoggerForFailedDispatch()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $logger = $this->getMockBuilder(EventLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects(self::once())
            ->method('onDispatchFailed')
            ->with($event);

        $eventBus = new SimpleEventBus([]);

        $eventBus->setLogger($logger);
        $eventBus->publish($event);
    }
}

class SimpleEventBusTest_Event extends Event
{
}
