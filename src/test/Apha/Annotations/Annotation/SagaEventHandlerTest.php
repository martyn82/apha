<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

class SagaEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Apha\Annotations\ReadOnlyException
     */
    public function setMethodNameIsWriteOnce()
    {
        $annotation = new SagaEventHandler(['associationProperty' => 'foo']);
        $annotation->setMethodName('bar');
        $annotation->setMethodName('baz');
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\ReadOnlyException
     */
    public function setEventTypeIsWriteOnce()
    {
        $annotation = new SagaEventHandler(['associationProperty' => 'foo']);
        $annotation->setEventType('bar');
        $annotation->setEventType('baz');
    }
}
