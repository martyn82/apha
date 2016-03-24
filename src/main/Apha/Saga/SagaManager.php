<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\EventHandling\EventHandler;
use Apha\Message\Event;

abstract class SagaManager implements EventHandler
{
    /**
     * @var SagaRepository
     */
    private $repository;

    /**
     * @var SagaFactory
     */
    private $factory;

    /**
     * @var AssociationValueResolver
     */
    private $associationValueResolver;

    /**
     * @var string[]
     */
    private $sagaTypes;

    /**
     * @param string[] $sagaTypes
     * @param SagaRepository $repository
     * @param AssociationValueResolver $associationValueResolver
     * @param SagaFactory $factory
     */
    public function __construct(
        array $sagaTypes,
        SagaRepository $repository,
        AssociationValueResolver $associationValueResolver,
        SagaFactory $factory
    )
    {
        $this->sagaTypes = $sagaTypes;
        $this->repository = $repository;
        $this->associationValueResolver = $associationValueResolver;
        $this->factory = $factory;
    }

    /**
     * @param Event $event
     */
    public function on(Event $event)
    {
        /* @var $sagaType string */
        foreach ($this->sagaTypes as $sagaType) {
            $associationValues = $this->extractAssociationValues($sagaType, $event);
            $sagaIdentities = [];

            /* @var $associationValue AssociationValue */
            foreach ($associationValues->getIterator() as $associationValue) {
                $sagaIdentities = $this->repository->find($sagaType, $associationValue);

                /* @var $identity Identity */
                foreach ($sagaIdentities as $identity) {
                    $saga = $this->repository->load($identity);
                    $saga->on($event);
                    $this->commit($saga);
                }
            }

            if (
                $this->getSagaCreationPolicy($sagaType, $event) == SagaCreationPolicy::ALWAYS || (
                    empty($sagaIdentities) &&
                    $this->getSagaCreationPolicy($sagaType, $event) == SagaCreationPolicy::IF_NONE_FOUND
                )
            ) {
                $saga = $this->factory->createSaga($sagaType, Identity::createNew(), $associationValues);
                $saga->on($event);
                $this->commit($saga);
            }
        }
    }

    /**
     * @return AssociationValueResolver
     */
    protected function getAssociationValueResolver(): AssociationValueResolver
    {
        return $this->associationValueResolver;
    }

    /**
     * @param string $sagaType
     * @param Event $event
     * @return AssociationValues
     */
    abstract protected function extractAssociationValues(string $sagaType, Event $event): AssociationValues;

    /**
     * @param string $sagaType
     * @param Event $event
     * @return int
     */
    abstract protected function getSagaCreationPolicy(string $sagaType, Event $event): int;

    /**
     * @param Saga $saga
     */
    protected function commit(Saga $saga)
    {
        $this->repository->commit($saga);
    }
}
