<?php
declare(strict_types = 1);

namespace Apha\CommandHandling;

use Apha\Message\Command;
use Psr\Log\LoggerInterface;

class LoggingCommandBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function sendIsDecoratedByLogger()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMockForAbstractClass();

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->getMockForAbstractClass();

        $command = $this->getMockBuilder(Command::class)
            ->getMockForAbstractClass();

        $commandBus->expects(self::once())
            ->method('send');

        $logger->expects(self::atLeastOnce())
            ->method('info');

        $decoratedCommandBus = new LoggingCommandBus($commandBus, $logger);
        $decoratedCommandBus->send($command);
    }
}
