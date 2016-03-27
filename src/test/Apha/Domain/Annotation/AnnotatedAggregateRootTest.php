<?php
declare(strict_types = 1);

namespace Apha\Domain\Annotation;

use Apha\Annotations\Annotation\AggregateIdentifier;
use Apha\Domain\Identity;
use Apha\Message\Events;

/**
 * @group aggregateroot
 * @group annotations
 */
class AnnotatedAggregateRootTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getIdRetrievesValueOfAggregateIdentifier()
    {
        $identity = Identity::createNew();

        /* @var $aggregate AnnotatedAggregateRootTest_Aggregate */
        $aggregate = AnnotatedAggregateRootTest_Aggregate::reconstruct(new Events());
        $aggregate->setIdentity($identity);

        $actualIdentity = $aggregate->getId();
        self::assertEquals($identity, $actualIdentity);
    }

    /**
     * @test
     * @expectedException \Apha\Domain\Annotation\NoAggregateIdentifierException
     */
    public function getIdThrowsExceptionIfNoAggregateIdentifierFound()
    {
        $aggregate = AnnotatedAggregateRootTest_AggregateWithoutIdentifier::reconstruct(new Events());
        $aggregate->getId();
    }

    /**
     * @test
     * @expectedException \Apha\Domain\Annotation\AmbiguousAggregateIdentifierException
     */
    public function getIdThrowsExceptionIfAmbiguousIdentifierFound()
    {
        $aggregate = AnnotatedAggregateRootTest_AggregateWithAmbiguousIdentifier::reconstruct(new Events());
        $aggregate->getId();
    }
}

class AnnotatedAggregateRootTest_Aggregate extends AnnotatedAggregateRoot
{
    /**
     * @AggregateIdentifier(type = "Apha\Domain\Identity")
     * @var Identity
     */
    protected $identity;

    /**
     * @param Identity $identity
     */
    public function setIdentity(Identity $identity)
    {
        $this->identity = $identity;
    }
}

class AnnotatedAggregateRootTest_AggregateWithoutIdentifier extends AnnotatedAggregateRoot
{
}

class AnnotatedAggregateRootTest_AggregateWithAmbiguousIdentifier extends AnnotatedAggregateRoot
{
    /**
     * @AggregateIdentifier(type = "string")
     * @var string
     */
    protected $id1;

    /**
     * @AggregateIdentifier(type = "string")
     * @var string
     */
    protected $id2;
}
