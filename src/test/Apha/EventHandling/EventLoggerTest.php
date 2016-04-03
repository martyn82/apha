<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;
use Psr\Log\LoggerInterface;

/**
 * @group eventhandling
 */
class EventLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function beforeDispatchIsLogged()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::once())
            ->method('info');

        $eventLogger = new EventLogger($logger);
        $eventLogger->onBeforeDispatch($event);
    }

    /**
     * @test
     */
    public function dispatchSuccessfulIsLogged()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::once())
            ->method('info');

        $eventLogger = new EventLogger($logger);
        $eventLogger->onDispatchSuccessful($event);
    }

    /**
     * @test
     */
    public function dispatchFailedIsLogged()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::once())
            ->method('warning');

        $eventLogger = new EventLogger($logger);
        $eventLogger->onDispatchFailed($event);
    }
}
