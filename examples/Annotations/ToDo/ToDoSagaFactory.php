<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Annotations\ParameterResolver;
use Apha\Domain\Identity;
use Apha\Saga\Annotation\AnnotatedSagaFactory;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;
use Apha\Scheduling\EventScheduler;
use Psr\Log\LoggerInterface;

class ToDoSagaFactory extends AnnotatedSagaFactory
{
    /**
     * @var EventScheduler
     */
    private $scheduler;

    /**
     * @var LoggerInterface
     */
    private $logger;
    
    /**
     * @param EventScheduler $scheduler
     * @param ParameterResolver $parameterResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        EventScheduler $scheduler,
        ParameterResolver $parameterResolver,
        LoggerInterface $logger
    )
    {
        parent::__construct($parameterResolver);
        $this->scheduler = $scheduler;
        $this->logger = $logger;
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
        /* @var $saga ToDoSaga */
        $saga = parent::createSaga($sagaType, $identity, $associationValues);
        $saga->setLogger($this->logger);
        $saga->setEventScheduler($this->scheduler);
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

    /**
     * @param Saga $saga
     * @throws \InvalidArgumentException
     */
    public function hydrate(Saga $saga)
    {
        /* @var $saga ToDoSaga */
        parent::hydrate($saga);
        $saga->setLogger($this->logger);
        $saga->setEventScheduler($this->scheduler);
    }
}