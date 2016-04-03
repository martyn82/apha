<?php
declare(strict_types = 1);

namespace Apha\CommandHandling;

use Apha\Message\Command;
use Psr\Log\LoggerInterface;

/**
 * @group commandhandling
 */
class CommandLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function beforeDispatchIsLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::once())
            ->method('info');

        $commandLogger = new CommandLogger($logger);
        $commandLogger->onBeforeDispatch($command);
    }

    /**
     * @test
     */
    public function dispatchSuccessfulIsLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::once())
            ->method('info');

        $commandLogger = new CommandLogger($logger);
        $commandLogger->onDispatchSuccessful($command);
    }

    /**
     * @test
     */
    public function dispatchFailedIsLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::once())
            ->method('error');

        $commandLogger = new CommandLogger($logger);
        $commandLogger->onDispatchFailed($command, new \Exception('Something is wrong.'));
    }
}
