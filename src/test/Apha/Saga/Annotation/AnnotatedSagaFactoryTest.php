<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Annotations\DefaultParameterResolver;
use Apha\Annotations\ParameterResolver;
use Apha\Domain\Identity;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;

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

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function hydrateSagaThrowsExceptionIfSagaUnsupported()
    {
        $parameterResolver = new DefaultParameterResolver();

        $saga = $this->getMockBuilder(Saga::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = new AnnotatedSagaFactory($parameterResolver);
        $factory->hydrate($saga);
    }

    /**
     * @test
     */
    public function hydrateSetsParameterResolverToAnnotatedSaga()
    {
        $parameterResolver = new DefaultParameterResolver();

        $saga = new AnnotatedSagaFactoryTest_Saga();

        $factory = new AnnotatedSagaFactory($parameterResolver);
        $factory->hydrate($saga);

        self::assertEquals($parameterResolver, $saga->getParameterResolver());
    }
}

class AnnotatedSagaFactoryTest_Saga extends AnnotatedSaga
{
    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @param ParameterResolver $parameterResolver
     */
    public function setParameterResolver(ParameterResolver $parameterResolver)
    {
        parent::setParameterResolver($parameterResolver);
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * @return ParameterResolver
     */
    public function getParameterResolver(): ParameterResolver
    {
        return $this->parameterResolver;
    }
}
