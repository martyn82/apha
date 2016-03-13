<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\EventStore\EventDescriptor;
use Apha\Message\Event;
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
            $associationValues
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

        $events = array_map(
            function (Event $event) use ($saga): EventDescriptor {
                return EventDescriptor::record(
                    $saga->getId()->getValue(),
                    get_class($saga),
                    $event->getEventName(),
                    $this->serializer->serialize($event),
                    $event->getVersion()
                );
            },
            $saga->getUncommittedChanges()->getArrayCopy()
        );

        $associationValues = $this->associationValuesToArray($saga->getAssociationValues());

        $this->storage->update(
            get_class($saga),
            $saga->getId()->getValue(),
            $associationValues,
            $events
        );

        $saga->markChangesCommitted();
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

        $sagaType = $sagaData['type'];
        $identity = Identity::fromString($sagaData['identity']);

        $associationValues = new AssociationValues([]);

        foreach ($sagaData['associations'] as $field => $value) {
            $associationValues->add(new AssociationValue($field, $value));
        }

        return new $sagaType($identity, $associationValues);
    }
}