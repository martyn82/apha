<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Annotations\DefaultParameterResolver;
use Apha\Domain\Identity;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;

class AnnotatedSagaFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createSagaCreatesAnnotatedSaga()
    {
        $identity = Identity::createNew();
        $associationValues = new AssociationValues([
            new AssociationValue('foo', 'bar')
        ]);

        $parameterResolver = new DefaultParameterResolver();

        $factory = new AnnotatedSagaFactory($parameterResolver);
        $saga = $factory->createSaga(AnnotatedSagaFactoryTest_Saga::class, $identity, $associationValues);

        self::assertEquals($identity->getValue(), $saga->getId()->getValue());
        self::assertEquals($associationValues, $saga->getAssociationValues());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function createSagaThrowsExceptionIfSagaUnsupported()
    {
        $parameterResolver = new DefaultParameterResolver();

        $factory = new AnnotatedSagaFactory($parameterResolver);
        $factory->createSaga(\stdClass::class, Identity::createNew(), new AssociationValues([]));
    }
}

class AnnotatedSagaFactoryTest_Saga extends AnnotatedSaga
{
}
