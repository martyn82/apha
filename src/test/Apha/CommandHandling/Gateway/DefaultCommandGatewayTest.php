<?php
declare(strict_types = 1);

namespace Apha\CommandHandling\Gateway;

use Apha\CommandHandling\CommandBus;
use Apha\CommandHandling\Interceptor\CommandDispatchInterceptor;
use Apha\CommandHandling\NoCommandHandlerException;
use Apha\Message\Command;

/**
 * @group commandhandling
 */
class DefaultCommandGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function sendNotifiesInterceptorsOfBeforeDispatch()
    {
        $interceptor = $this->getMockBuilder(CommandDispatchInterceptor::class)
            ->getMock();

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->getMock();

        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $interceptor->expects(self::once())
            ->method('onBeforeDispatch')
            ->with($command);

        $gateway = new DefaultCommandGateway($commandBus, [$interceptor]);
        $gateway->send($command);
    }

    /**
     * @test
     */
    public function sendNotifiesInterceptorsOfSuccessfulDispatch()
    {
        $interceptor = $this->getMockBuilder(CommandDispatchInterceptor::class)
            ->getMock();

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->getMock();

        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $interceptor->expects(self::once())
            ->method('onDispatchSuccessful')
            ->with($command);

        $gateway = new DefaultCommandGateway($commandBus, [$interceptor]);
        $gateway->send($command);
    }

    /**
     * @test
     */
    public function sendNotifiesInterceptorsOfFailedDispatch()
    {
        $interceptor = $this->getMockBuilder(CommandDispatchInterceptor::class)
            ->getMock();

        $commandBus = $this->getMockBuilder(CommandBus::class)
            ->getMock();

        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $exception = new NoCommandHandlerException($command);

        $commandBus->expects(self::once())
            ->method('send')
            ->with($command)
            ->willThrowException($exception);

        $interceptor->expects(self::once())
            ->method('onDispatchFailed')
            ->with($command, $exception);

        $gateway = new DefaultCommandGateway($commandBus, [$interceptor]);
        $gateway->send($command);
    }
}