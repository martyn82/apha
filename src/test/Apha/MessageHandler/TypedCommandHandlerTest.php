<?php

namespace Apha\MessageHandler;

use Apha\Domain\Message\Command;

class TypedCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandleWithTypeInflection()
    {
        $handler = $this->getMockBuilder(TypedCommandHandler::class)
            ->setMethods(['handleSomeCommand'])
            ->getMockForTrait();

        $command = $this->getMockBuilder(Command::class)
            ->setMockClassName('SomeCommand')
            ->getMock();

        $handler->expects(self::once())
            ->method('handleSomeCommand')
            ->with($command);

        $handler->handle($command);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHandleThrowsExceptionForUnknownCommand()
    {
        $handler = $this->getMockBuilder(TypedCommandHandler::class)
            ->getMockForTrait();

        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $handler->handle($command);
    }
}