<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\CommandHandler;
use Apha\Annotations\Annotation\EventHandler;
use Apha\Message\Command;
use Apha\Message\Event;

class EventHandlerAnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function readAllRetrievesAllAnnotatedEventHandlers()
    {
        $handler = new EventHandlerAnnotationReaderTest_EventHandler();
        $reader = new EventHandlerAnnotationReader($handler);
        $annotations = $reader->readAll();

        self::assertCount(1, $annotations);
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\AnnotationReaderException
     */
    public function readAllThrowsExceptionIfNoParameterToPass()
    {
        $handler = new EventHandlerAnnotationReaderTest_EventHandlerWithoutParameter();
        $reader = new EventHandlerAnnotationReader($handler);
        $reader->readAll();
    }
}

class EventHandlerAnnotationReaderTest_EventHandler implements \Apha\EventHandling\EventHandler
{
    /**
     * @EventHandler()
     * @param Event $event
     * @return void
     */
    public function on(Event $event)
    {
    }

    /**
     * @CommandHandler()
     * @param Command $command
     */
    private function handle(Command $command)
    {
    }
}

class EventHandlerAnnotationReaderTest_EventHandlerWithoutParameter implements \Apha\EventHandling\EventHandler
{
    /**
     * @param Event $event
     * @return void
     */
    public function on(Event $event)
    {
    }

    /**
     * @EventHandler()
     */
    private function onNothing()
    {
        // invalid
    }
}
