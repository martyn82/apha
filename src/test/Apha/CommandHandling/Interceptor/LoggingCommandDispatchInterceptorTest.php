<?php
declare(strict_types = 1);

namespace Apha\CommandHandling\Interceptor;

use Apha\Message\Command;
use Psr\Log\LoggerInterface;

class LoggingCommandDispatchInterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function onBeforeDispatchIsLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::once())
            ->method('info');

        $interceptor = new LoggingCommandDispatchInterceptor($logger);
        $interceptor->onBeforeDispatch($command);
    }

    /**
     * @test
     */
    public function onDispatchSuccessfulIsNotLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::never())
            ->method(self::anything());

        $interceptor = new LoggingCommandDispatchInterceptor($logger);
        $interceptor->onDispatchSuccessful($command);
    }

    /**
     * @test
     */
    public function onDispatchFailedIsLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();

        $logger->expects(self::once())
            ->method('error');

        $interceptor = new LoggingCommandDispatchInterceptor($logger);
        $interceptor->onDispatchFailed($command, new \Exception());
    }
}