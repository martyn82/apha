<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Annotations\ParameterResolver;
use Apha\Domain\Identity;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;
use Apha\Saga\SagaFactory;

class AnnotatedSagaFactory implements SagaFactory
{
    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @param ParameterResolver $parameterResolver
     */
    public function __construct(ParameterResolver $parameterResolver)
    {
        $this->parameterResolver = $parameterResolver;
    }

    /**
     * @param string $sagaType
     * @param Identity $identity
     * @param AssociationValues $associationValues
     * @return Saga
     * @throws \InvalidArgumentException
     */
    public function createSaga(string $sagaType, Identity $identity, AssociationValues $associationValues): Saga
    {
        if (!$this->supports($sagaType)) {
            throw new \InvalidArgumentException("This factory does not support Sagas of type '{$sagaType}'.");
        }

        /* @var $saga AnnotatedSaga */
        $saga = new $sagaType($identity);
        $saga->setParameterResolver($this->parameterResolver);

        /* @var $associationValue AssociationValue */
        foreach ($associationValues as $associationValue) {
            $saga->associateWith($associationValue);
        }

        return $saga;
    }

    /**
     * @param string $sagaType
     * @return bool
     */
    public function supports(string $sagaType): bool
    {
        return is_subclass_of($sagaType, AnnotatedSaga::class, true);
    }

    /**
     * @param Saga $saga
     * @throws \InvalidArgumentException
     */
    public function hydrate(Saga $saga)
    {
        if (!$this->supports(get_class($saga))) {
            $sagaType = get_class($saga);
            throw new \InvalidArgumentException("This factory does not support Sagas of type '{$sagaType}'.");
        }

        /* @var $saga AnnotatedSaga */
        $saga->setParameterResolver($this->parameterResolver);
    }
}
