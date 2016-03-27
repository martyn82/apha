<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\SagaEventHandler;
use Apha\Domain\Identity;
use Apha\Message\Event;
use Apha\Saga\Annotation\AnnotatedSaga;

class SagaEventHandlerAnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function readAllRetrievesAllAnnotatedSagaEventHandlers()
    {
        $identity = Identity::createNew();
        $saga = new SagaEventHandlerAnnotationReaderTest_SagaEventHandler($identity);
        $reader = new SagaEventHandlerAnnotationReader($saga);
        $annotations = $reader->readAll();

        self::assertCount(1, $annotations);
        self::assertEquals("processId", $annotations[0]->getAssociationProperty());
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\AnnotationReaderException
     */
    public function readAllThrowsExceptionIfNoParameter()
    {
        $identity = Identity::createNew();
        $saga = new SagaEventHandlerAnnotationReaderTest_SagaEventHandlerWithoutParameter($identity);
        $reader = new SagaEventHandlerAnnotationReader($saga);
        $reader->readAll();
    }

    /**
     * @test
     * @expectedException \Doctrine\Common\Annotations\AnnotationException
     */
    public function readAllThrowsExceptionIfNoAssociationPropertySet()
    {
        $identity = Identity::createNew();
        $saga = new SagaEventHandlerAnnotationReaderTest_SagaEventHandlerWithoutProperty($identity);
        $reader = new SagaEventHandlerAnnotationReader($saga);
        $reader->readAll();
    }
}

class SagaEventHandlerAnnotationReaderTest_SagaEventHandler extends AnnotatedSaga
{
    /**
     * @SagaEventHandler(associationProperty = "processId")
     * @param ProcessStarted $event
     */
    public function onProcessStarted(ProcessStarted $event)
    {
    }
}

class SagaEventHandlerAnnotationReaderTest_SagaEventHandlerWithoutParameter extends AnnotatedSaga
{
    /**
     * @SagaEventHandler(associationProperty = "processId")
     */
    public function onProcessStarted()
    {
    }
}

class SagaEventHandlerAnnotationReaderTest_SagaEventHandlerWithoutProperty extends AnnotatedSaga
{
    /**
     * @SagaEventHandler()
     */
    public function onProcessStarted()
    {
    }
}

class ProcessStarted extends Event
{
}