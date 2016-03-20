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
     */
    public function createSaga(string $sagaType, Identity $identity, AssociationValues $associationValues = null): Saga
    {
        return new $sagaType();
    }

    /**
     * @param string $sagaType
     * @return bool
     */
    public function supports(string $sagaType): bool
    {
        // the naive way
        return true;
    }
}