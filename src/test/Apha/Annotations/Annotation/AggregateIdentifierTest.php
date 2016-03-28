<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

class AggregateIdentifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException \Apha\Annotations\ReadOnlyException
     */
    public function setPropertyNameIsWriteOnce()
    {
        $annotation = new AggregateIdentifier(['type' => 'foo']);
        $annotation->setPropertyName('bar');
        $annotation->setPropertyName('baz');
    }
}
