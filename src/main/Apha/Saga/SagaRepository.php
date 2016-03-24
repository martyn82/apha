<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\Saga\Storage\SagaStorage;
use Apha\Serializer\Serializer;

class SagaRepository
{
    /**
     * @var SagaStorage
     */
    private $storage;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @param SagaStorage $storage
     * @param Serializer $serializer
     */
    public function __construct(SagaStorage $storage, Serializer $serializer)
    {
        $this->storage = $storage;
        $this->serializer = $serializer;
    }

    /**
     * @param Saga $saga
     * @return void
     */
    public function add(Saga $saga)
    {
        if (!$saga->isActive()) {
            return;
        }

        $associationValues = $this->associationValuesToArray($saga->getAssociationValues());

        $this->storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $associationValues,
            $this->serializer->serialize($saga)
        );
    }

    /**
     * @param Saga $saga
     * @return void
     */
    public function commit(Saga $saga)
    {
        if (!$saga->isActive()) {
            $this->storage->delete($saga->getId()->getValue());
            return;
        }

        $associationValues = $this->associationValuesToArray($saga->getAssociationValues());

        $this->storage->update(
            get_class($saga),
            $saga->getId()->getValue(),
            $associationValues,
            $this->serializer->serialize($saga)
        );
    }

    /**
     * @param AssociationValues $associationValues
     * @return array
     */
    private function associationValuesToArray(AssociationValues $associationValues): array
    {
        return array_reduce(
            $associationValues->getArrayCopy(),
            function (array $accumulator, AssociationValue $item): array {
                $accumulator[$item->getKey()] = $item->getValue();
                return $accumulator;
            },
            []
        );
    }

    /**
     * @param string $sagaType
     * @param AssociationValue $associationValue
     * @return Identity[]
     */
    public function find(string $sagaType, AssociationValue $associationValue): array
    {
        return array_map(
            function (array $sagaData): Identity {
                return Identity::fromString($sagaData['identity']);
            },
            $this->storage->find($sagaType, [$associationValue->getKey() => $associationValue->getValue()])
        );
    }

    /**
     * @param Identity $sagaIdentity
     * @return Saga
     */
    public function load(Identity $sagaIdentity)
    {
        $sagaData = $this->storage->findById($sagaIdentity->getValue());

        if (empty($sagaData)) {
            return null;
        }

        return $this->serializer->deserialize($sagaData['serialized'], $sagaData['type']);
    }
}