<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\Message\Event;

class SimpleAssociationValueResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function extractAssociationValuesOnIdentity()
    {
        $identity = Identity::createNew();
        $event = new SimpleAssociationValueResolverTest_Event($identity);
        $resolver = new SimpleAssociationValueResolver();
        $associationValues = $resolver->extractAssociationValues($event);

        $associationValuesArray = $associationValues->getArrayCopy();
        self::assertEquals($identity->getValue(), $associationValuesArray[0]->getValue());
    }
}

class SimpleAssociationValueResolverTest_Event extends Event
{
    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->setIdentity($identity);
    }
}