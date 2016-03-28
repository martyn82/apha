<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;

class GenericSagaFactory implements SagaFactory
{
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

        return new $sagaType($identity, $associationValues);
    }

    /**
     * @param string $sagaType
     * @return bool
     */
    public function supports(string $sagaType): bool
    {
        return is_subclass_of($sagaType, Saga::class, true);
    }
}