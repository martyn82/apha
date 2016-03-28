<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

class EndSagaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Apha\Annotations\ReadOnlyException
     */
    public function setMethodNameIsWriteOnce()
    {
        $annotation = new EndSaga();
        $annotation->setMethodName('bar');
        $annotation->setMethodName('baz');
    }
}
