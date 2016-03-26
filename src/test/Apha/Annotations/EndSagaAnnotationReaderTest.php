<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\EndSaga;
use Apha\Domain\Identity;
use Apha\Message\Event;
use Apha\Saga\Annotation\AnnotatedSaga;
use Apha\Saga\AssociationValues;

class EndSagaAnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function readAllRetrievesAllAnnotatedStartSagas()
    {
        $saga = new EndSagaAnnotationReaderTest_Saga(Identity::createNew(), new AssociationValues([]));
        $reader = new EndSagaAnnotationReader($saga);
        $annotations = $reader->readAll();

        self::assertCount(1, $annotations);
    }
}

class EndSagaAnnotationReaderTest_Saga extends AnnotatedSaga
{
    /**
     * @EndSaga()
     * @param ProcessEnded $event
     */
    public function onProcessEnded(ProcessEnded $event)
    {
    }
}

class ProcessEnded extends Event
{
}
