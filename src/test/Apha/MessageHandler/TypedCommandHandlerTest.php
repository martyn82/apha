<?php
declare(strict_types=1);

namespace Apha\MessageHandler;

use Apha\Message\Command;

class TypedCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function handleWithTypeInflection()
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
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function handleThrowsExceptionForUnknownCommand()
    {
        $handler = $this->getMockBuilder(TypedCommandHandler::class)
            ->getMockForTrait();

        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $handler->handle($command);
    }
}