<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\AggregateIdentifier;
use Apha\Domain\Annotation\AnnotatedAggregateRoot;
use Apha\Domain\Identity;
use Apha\Message\Events;

class AggregateIdentifierAnnotationReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function readAllRetrievesAllAnnotatedAggregateIdentifiers()
    {
        $aggregate = AggregateIdentifierAnnotationReaderTest_AggregateRoot::reconstruct(new Events());
        $reader = new AggregateIdentifierAnnotationReader($aggregate);
        $annotations = $reader->readAll();

        self::assertCount(1, $annotations);
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\AnnotationReaderException
     */
    public function readAllThrowsExceptionIfPropertyIsPrivate()
    {
        $aggregate = AggregateIdentifierAnnotationReaderTest_AggregateRootInvalidAccessibility::reconstruct(
            new Events()
        );
        $reader = new AggregateIdentifierAnnotationReader($aggregate);
        $reader->readAll();
    }

    /**
     * @test
     * @expectedException \Apha\Annotations\AnnotationReaderException
     */
    public function readAllThrowsExceptionIfAggregateIdentifierTypeIsUnknown()
    {
        $aggregate = AggregateIdentifierAnnotationReaderTest_AggregateRootInvalidType::reconstruct(new Events());
        $reader = new AggregateIdentifierAnnotationReader($aggregate);
        $reader->readAll();
    }
}

class AggregateIdentifierAnnotationReaderTest_AggregateRoot extends AnnotatedAggregateRoot
{
    /**
     * @AggregateIdentifier(type = "Apha\Domain\Identity")
     * @var Identity
     */
    protected $identity;
}

class AggregateIdentifierAnnotationReaderTest_AggregateRootInvalidAccessibility extends AnnotatedAggregateRoot
{
    /**
     * @AggregateIdentifier(type = "string")
     * @var string
     */
    private $identity;
}

class AggregateIdentifierAnnotationReaderTest_AggregateRootInvalidType extends AnnotatedAggregateRoot
{
    /**
     * @AggregateIdentifier(type = "foo")
     * @var Identity
     */
    private $identity;
}
