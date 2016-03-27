<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\CommandHandler;
use Apha\Annotations\Annotation\EventHandler;
use Apha\Message\Command;
use Apha\Message\Event;

/**
 * @group annotations
 */
class CommandHandlerAnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function readAllRetrievesAllAnnotatedCommandHandlers()
    {
        $handler = new CommandHandlerAnnotationReaderTest_CommandHandler();
        $reader = new CommandHandlerAnnotationReader($handler);
        $annotations = $reader->readAll();

        self::assertCount(1, $annotations);
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\AnnotationReaderException
     */
    public function readAllThrowsExceptionIfNoParameterToPass()
    {
        $handler = new CommandHandlerAnnotationReaderTest_CommandHandlerWithoutParameter();
        $reader = new CommandHandlerAnnotationReader($handler);
        $reader->readAll();
    }
}

class CommandHandlerAnnotationReaderTest_CommandHandler implements \Apha\CommandHandling\CommandHandler
{
    /**
     * @CommandHandler()
     * @param Command $command
     */
    public function handle(Command $command)
    {
    }

    /**
     * @EventHandler()
     * @param Event $event
     */
    private function handleSomethingElse(Event $event)
    {
    }
}

class CommandHandlerAnnotationReaderTest_CommandHandlerWithoutParameter implements \Apha\CommandHandling\CommandHandler
{
    /**
     * @param Command $command
     */
    public function handle(Command $command)
    {
    }

    /**
     * @CommandHandler()
     */
    private function handleNothing()
    {
        // invalid
    }
}
