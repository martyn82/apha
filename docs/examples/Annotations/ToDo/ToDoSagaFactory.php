<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Domain\Identity;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;
use Apha\Saga\SagaFactory;
use Apha\Scheduling\EventScheduler;

class ToDoSagaFactory implements SagaFactory
{
    /**
     * @var EventScheduler
     */
    private $scheduler;

    /**
     * @param EventScheduler $scheduler
     */
    public function __construct(EventScheduler $scheduler)
    {
        $this->scheduler = $scheduler;
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
            throw new \InvalidArgumentException("Unsupported saga type: '{$sagaType}'.'");
        }
        
        /* @var $saga ToDoSaga */
        $saga = new $sagaType($identity);
        $saga->setEventScheduler($this->scheduler);

        /* @var $associationValue AssociationValue */
        foreach ($associationValues->getIterator() as $associationValue) {
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
        return $sagaType == ToDoSaga::class;
    }
}