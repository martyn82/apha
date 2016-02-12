<?php

namespace Apha\MessageBus;

use Apha\Domain\Message\Command;
use Apha\MessageHandler\CommandHandler;

class CommandBusTest extends \PHPUnit_Framework_TestCase
{
    public function testSendWithCommandWillCallHandleOnAppropriateCommandHandler()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMockForAbstractClass();

        $handler = $this->getMockBuilder(CommandHandler::class)
            ->setMethods(['handle'])
            ->getMockForAbstractClass();

        $commandHandlerMap = [
            get_class($command) => $handler
        ];

        $handler->expects(self::once())
            ->method('handle')
            ->with($command);

        $commandBus = new CommandBus($commandHandlerMap);
        $commandBus->send($command);
    }

    /**
     * @expectedException \Apha\MessageBus\NoCommandHandlerException
     */
    public function testSendThrowsExceptionIfNoHandlerForCommand()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMockForAbstractClass();

        $commandBus = new CommandBus([]);
        $commandBus->send($command);
    }
}