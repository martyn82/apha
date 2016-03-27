<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;
use Psr\Log\LoggerInterface;

/**
 * @group eventhandling
 */
class LoggingEventBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function publishIsDecoratedByLogger()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $eventBus = $this->getMockBuilder(EventBus::class)
            ->getMockForAbstractClass();

        $event = $this->getMockBuilder(Event::class)
            ->getMockForAbstractClass();

        $eventBus->expects(self::once())
            ->method('publish');

        $logger->expects(self::atLeastOnce())
            ->method('info');

        $decoratedEventBus = new LoggingEventBus($eventBus, $logger);
        $decoratedEventBus->publish($event);
    }

    /**
     * @test
     */
    public function publishReturnsTrueIfEventWasSuccessfullyPublished()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $eventBus = $this->getMockBuilder(EventBus::class)
            ->getMockForAbstractClass();

        $event = $this->getMockBuilder(Event::class)
            ->getMockForAbstractClass();

        $eventBus->expects(self::once())
            ->method('publish')
            ->willReturn(true);

        $logger->expects(self::atLeastOnce())
            ->method('info');

        $decoratedEventBus = new LoggingEventBus($eventBus, $logger);
        self::assertTrue($decoratedEventBus->publish($event));
    }
}
