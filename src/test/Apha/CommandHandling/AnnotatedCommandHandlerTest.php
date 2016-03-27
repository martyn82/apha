<?php
declare(strict_types = 1);

namespace Apha\CommandHandling;

use Apha\Annotations\Annotation as Apha;
use Apha\Message\Command;

/**
 * @group annotations
 * @group commandhandling
 */
class AnnotatedCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function handleWithAnnotations()
    {
        $handler = new AnnotatedCommandHandlerTest_CommandHandler();
        $command = new AnnotatedCommandHandlerTest_Command();
        $handler->handle($command);

        self::assertTrue($handler->isHandled());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function handleThrowsExceptionForUnknownCommand()
    {
        $handler = new AnnotatedCommandHandlerTest_CommandHandler();

        $command = $this->getMockBuilder(Command::class)
            ->getMock();

        $handler->handle($command);
    }

    /**
     * @test
     * @expectedException \Apha\CommandHandling\CommandHandlerAlreadyExistsException
     */
    public function handleThrowsExceptionForMultipleHandlersForSameCommand()
    {
        $handler = new AnnotatedCommandHandlerTest_CommandHandlerAmbiguous();
        $command = new AnnotatedCommandHandlerTest_Command();
        $handler->handle($command);
    }
}

class AnnotatedCommandHandlerTest_CommandHandler implements \Apha\CommandHandling\CommandHandler
{
    use AnnotatedCommandHandler;

    /**
     * @var bool
     */
    private $handled = false;

    /**
     * @return bool
     */
    public function isHandled(): bool
    {
        return $this->handled;
    }

    /**
     * @Apha\CommandHandler()
     * @param AnnotatedCommandHandlerTest_Command $command
     */
    public function handleSomeCommand(AnnotatedCommandHandlerTest_Command $command)
    {
        $this->handled = true;
    }
}

class AnnotatedCommandHandlerTest_CommandHandlerAmbiguous implements CommandHandler
{
    use AnnotatedCommandHandler;

    /**
     * @Apha\CommandHandler()
     * @param AnnotatedCommandHandlerTest_Command $command
     */
    public function handleSomeCommand1(AnnotatedCommandHandlerTest_Command $command)
    {
    }

    /**
     * @Apha\CommandHandler()
     * @param AnnotatedCommandHandlerTest_Command $command
     */
    public function handleSomeCommand2(AnnotatedCommandHandlerTest_Command $command)
    {
    }
}

class AnnotatedCommandHandlerTest_Command extends Command
{
}