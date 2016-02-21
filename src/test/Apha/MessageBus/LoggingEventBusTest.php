<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Event;
use Psr\Log\LoggerInterface;

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

        $logger->expects(self::exactly(2))
            ->method('info');

        $decoratedEventBus = new LoggingEventBus($eventBus, $logger);
        $decoratedEventBus->publish($event);
    }
}
