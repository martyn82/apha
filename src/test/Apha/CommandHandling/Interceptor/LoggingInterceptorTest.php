<?php
declare(strict_types = 1);

namespace Apha\CommandHandling\Interceptor;

use Apha\CommandHandling\CommandLogger;
use Apha\Message\Command;

/**
 * @group commandhandling
 */
class LoggingInterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function onBeforeDispatchIsLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(CommandLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects(self::once())
            ->method('onBeforeDispatch');

        $interceptor = new LoggingInterceptor($logger);
        $interceptor->onBeforeDispatch($command);
    }

    /**
     * @test
     */
    public function onDispatchSuccessfulIsNotLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(CommandLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects(self::once())
            ->method('onDispatchSuccessful');

        $interceptor = new LoggingInterceptor($logger);
        $interceptor->onDispatchSuccessful($command);
    }

    /**
     * @test
     */
    public function onDispatchFailedIsLogged()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $logger = $this->getMockBuilder(CommandLogger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $logger->expects(self::once())
            ->method('onDispatchFailed');

        $interceptor = new LoggingInterceptor($logger);
        $interceptor->onDispatchFailed($command, new \Exception());
    }
}
