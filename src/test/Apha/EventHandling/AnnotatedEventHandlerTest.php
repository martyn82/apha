<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Annotations\Annotation as Apha;
use Apha\Message\Event;

/**
 * @group annotations
 * @group eventhandling
 */
class AnnotatedEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function handleWithAnnotations()
    {
        $handler = new AnnotatedEventHandlerTest_EventHandler();
        $event = new AnnotatedEventHandlerTest_Event();
        $handler->on($event);

        self::assertTrue($handler->isHandled());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function handleThrowsExceptionForUnknownEvent()
    {
        $handler = new AnnotatedEventHandlerTest_EventHandler();

        $event = $this->getMockBuilder(Event::class)
            ->getMockForAbstractClass();

        $handler->on($event);
    }
}

class AnnotatedEventHandlerTest_EventHandler implements EventHandler
{
    use AnnotatedEventHandler;

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
     * @Apha\EventHandler()
     * @param AnnotatedEventHandlerTest_Event $event
     */
    public function handleSomeEvent(AnnotatedEventHandlerTest_Event $event)
    {
        $this->handled = true;
    }
}

class AnnotatedEventHandlerTest_Event extends Event
{
}
