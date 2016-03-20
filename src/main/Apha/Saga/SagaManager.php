<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\EventHandling\EventBus;
use Apha\EventHandling\EventHandler;
use Apha\Message\Event;

abstract class SagaManager implements EventHandler
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var SagaRepository
     */
    private $repository;

    /**
     * @var SagaFactory
     */
    private $factory;

    /**
     * @var string[]
     */
    private $sagaTypes;

    /**
     * @param EventBus $eventBus
     * @param SagaRepository $repository
     * @param SagaFactory $factory
     * @param string[] $sagaTypes
     */
    public function __construct(EventBus $eventBus, SagaRepository $repository, SagaFactory $factory, array $sagaTypes)
    {
        $this->eventBus = $eventBus;
        $this->repository = $repository;
        $this->factory = $factory;
        $this->sagaTypes = $sagaTypes;

        $this->subscribe();
    }

    /**
     */
    public function __destruct()
    {
        $this->unsubscribe();
    }

    /**
     */
    public function subscribe()
    {
        $this->eventBus->addHandler(Event::class, $this);
    }

    /**
     */
    public function unsubscribe()
    {
        $this->eventBus->removeHandler(Event::class, $this);
    }

    /**
     * @param Event $event
     */
    public function on(Event $event)
    {
        foreach ($this->sagaTypes as $sagaType) {
            /* @var $sagaType string */
            $associationValue = $this->extractAssociationValue($sagaType, $event);

            /* @var $sagaIdentities Identity[] */
            $sagaIdentities = $this->repository->find($sagaType, $associationValue);

            foreach ($sagaIdentities as $identity) {
                /* @var $identity Identity */
                $saga = $this->repository->load($identity);
                $saga->on($event);
                $this->commit($saga);
            }
        }
    }

    /**
     * @param string $sagaType
     * @param Event $event
     * @return AssociationValue
     */
    abstract protected function extractAssociationValue(string $sagaType, Event $event): AssociationValue;

    /**
     * @param Saga $saga
     */
    protected function commit(Saga $saga)
    {
        $this->repository->commit($saga);
    }
}
