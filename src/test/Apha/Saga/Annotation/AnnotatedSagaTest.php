<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Annotations\Annotation\EndSaga;
use Apha\Annotations\Annotation\SagaEventHandler;
use Apha\Annotations\Annotation\StartSaga;
use Apha\Domain\Identity;
use Apha\Message\Event;

/**
 * @group saga
 * @group annotations
 */
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
        $saga = new AnnotatedSagaTest_Saga(Identity::createNew());

        $event = $this->getMockBuilder(Event::class)
            ->getMockForAbstractClass();

        $saga->on($event);
    }

    /**
     * @test
     */
    public function startSagaAnnotationStartsTheSaga()
    {
        $saga = new AnnotatedSagaTest_Saga(Identity::createNew());
        $saga->on(new AnnotatedSagaTest_StartSaga());

        self::assertTrue($saga->isActive());
    }

    /**
     * @test
     * @expectedException \Apha\Saga\Annotation\NoStartSagaHandlerException
     */
    public function onThrowsExceptionIfSagaCannotBeStarted()
    {
        $saga = new AnnotatedSagaTest_SagaWithoutStart(Identity::createNew());
        $saga->on(new AnnotatedSagaTest_StartSaga());
    }

    /**
     * @test
     */
    public function endSagaAnnotationEndsTheSaga()
    {
        $saga = new AnnotatedSagaTest_Saga();
        $saga->on(new AnnotatedSagaTest_EndSaga());

        self::assertFalse($saga->isActive());
    }

    /**
     * @test
     * @expectedException \Apha\Saga\Annotation\NoEndSagaHandlerException
     */
    public function onThrowsExceptionIfSagaCannotBeEnded()
    {
        $saga = new AnnotatedSagaTest_SagaWithoutEnd();
        $saga->on(new AnnotatedSagaTest_EndSaga());
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
     * @StartSaga()
     * @SagaEventHandler(associationProperty = "eventId")
     * @param AnnotatedSagaTest_StartSaga $event
     */
    public function onSagaStart(AnnotatedSagaTest_StartSaga $event)
    {
    }

    /**
     * @EndSaga()
     * @SagaEventHandler(associationProperty = "eventId")
     * @param AnnotatedSagaTest_EndSaga $event
     */
    public function onSagaEnd(AnnotatedSagaTest_EndSaga $event)
    {
    }

    /**
     * @return bool
     */
    public function isHandled(): bool
    {
        return $this->handled;
    }
}

class AnnotatedSagaTest_SagaWithoutStart extends AnnotatedSaga
{
    /**
     * @SagaEventHandler(associationProperty = "eventId")
     * @param AnnotatedSagaTest_StartSaga $event
     */
    public function onStartSaga(AnnotatedSagaTest_StartSaga $event)
    {
    }
}

class AnnotatedSagaTest_SagaWithoutEnd extends AnnotatedSaga
{
    /**
     * @StartSaga()
     * @SagaEventHandler(associationProperty = "eventId")
     * @param AnnotatedSagaTest_EndSaga $event
     */
    public function onEndSaga(AnnotatedSagaTest_EndSaga $event)
    {
    }
}

class AnnotatedSagaTest_StartSaga extends Event
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

class AnnotatedSagaTest_EndSaga extends Event
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