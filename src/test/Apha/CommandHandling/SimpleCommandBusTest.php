<?php
declare(strict_types = 1);

namespace Apha\CommandHandling;

use Apha\Message\Command;

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
     * @expectedException \Apha\CommandHandling\NoCommandHandlerException
     */
    public function sendThrowsExceptionIfNoHandlerForCommand()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMockForAbstractClass();

        $commandBus = new SimpleCommandBus([]);
        $commandBus->send($command);
    }

    /**
     * @test
     */
    public function addHandlerRegistersHandlerForCommand()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMockForAbstractClass();

        $handler = $this->getMockBuilder(CommandHandler::class)
            ->getMockForAbstractClass();

        $handler->expects(self::once())
            ->method('handle')
            ->with($command);

        $commandBus = new SimpleCommandBus([]);
        $commandBus->addHandler(get_class($command), $handler);

        $commandBus->send($command);
    }

    /**
     * @test
     * @expectedException \Apha\CommandHandling\CommandHandlerAlreadyExistsException
     */
    public function addHandlerThrowsExceptionIfCommandAlreadyHasAHandler()
    {
        $command = $this->getMockBuilder(Command::class)
            ->getMockForAbstractClass();

        $handler = $this->getMockBuilder(CommandHandler::class)
            ->getMockForAbstractClass();

        $commandBus = new SimpleCommandBus([
            get_class($command) => $handler
        ]);
        $commandBus->addHandler(get_class($command), $handler);
    }
}