<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Annotations\Annotation\SagaEventHandler;
use Apha\Domain\Identity;
use Apha\Message\Event;

class AnnotatedSagaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function handleWithAnnotations()
    {
        $handler = new AnnotatedSagaTest_Saga(Identity::createNew());
        $event = new AnnotatedSagaTest_Event();
        $handler->on($event);

        self::assertTrue($handler->isHandled());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function handleThrowsExceptionForUnknownEvent()
    {
        $handler = new AnnotatedSagaTest_Saga(Identity::createNew());

        $event = $this->getMockBuilder(Event::class)
            ->getMockForAbstractClass();

        $handler->on($event);
    }
}

class AnnotatedSagaTest_Saga extends AnnotatedSaga
{
    /**
     * @var bool
     */
    private $handled = false;

    /**
     * @SagaEventHandler(associationProperty = "eventId")
     * @param AnnotatedSagaTest_Event $event
     */
    public function onAnnotatedSagaTest_Event(AnnotatedSagaTest_Event $event)
    {
        $this->handled = true;
    }

    /**
     * @return bool
     */
    public function isHandled(): bool
    {
        return $this->handled;
    }
}

class AnnotatedSagaTest_Event extends Event
{
    /**
     * @var string
     */
    private $eventId = 'foo';

    /**
     * @return string
     */
    public function getEventId(): string
    {
        return $this->eventId;
    }
}