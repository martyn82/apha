<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;

interface SagaFactory
{
    /**
     * @param string $sagaType
     * @param Identity $identity
     * @param AssociationValues $associationValues
     * @return Saga
     * @throws \InvalidArgumentException
     */
    public function createSaga(string $sagaType, Identity $identity, AssociationValues $associationValues): Saga;

    /**
     * @param string $sagaType
     * @return bool
     */
    public function supports(string $sagaType): bool;
}
