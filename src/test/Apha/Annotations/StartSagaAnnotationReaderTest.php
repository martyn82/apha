<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\StartSaga;
use Apha\Domain\Identity;
use Apha\Message\Event;
use Apha\Saga\Annotation\AnnotatedSaga;
use Apha\Saga\AssociationValues;

/**
 * @group annotations
 */
class StartSagaAnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function readAllRetrievesAllAnnotatedStartSagas()
    {
        $saga = new StartSagaAnnotationReaderTest_Saga(Identity::createNew(), new AssociationValues([]));
        $reader = new StartSagaAnnotationReader($saga);
        $annotations = $reader->readAll();

        self::assertCount(1, $annotations);
    }
}

class StartSagaAnnotationReaderTest_Saga extends AnnotatedSaga
{
    /**
     * @StartSaga()
     * @param ProcessStarted $event
     */
    public function onProcessStarted(ProcessStarted $event)
    {
    }
}

class StartSagaAnnotationReaderTest_ProcessStarted extends Event
{
}
