<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Command;
use Apha\MessageHandler\CommandHandler;

class SimpleCommandBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function sendWithCommandWillCallHandleOnAppropriateCommandHandler()
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

        $commandBus = new SimpleCommandBus($commandHandlerMap);
        $commandBus->send($command);
    }

    /**
     * @test
     * @expectedException \Apha\MessageBus\NoCommandHandlerException
     */
    public function sendThrowsExceptionIfNoHandlerForCommand()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMockForAbstractClass();

        $commandBus = new SimpleCommandBus([]);
        $commandBus->send($command);
    }
}