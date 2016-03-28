<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

class EventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Apha\Annotations\ReadOnlyException
     */
    public function setMethodNameIsWriteOnce()
    {
        $annotation = new EventHandler();
        $annotation->setMethodName('bar');
        $annotation->setMethodName('baz');
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\ReadOnlyException
     */
    public function setEventTypeIsWriteOnce()
    {
        $annotation = new EventHandler();
        $annotation->setEventType('bar');
        $annotation->setEventType('baz');
    }
}
