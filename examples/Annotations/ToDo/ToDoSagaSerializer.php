<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Annotations\ParameterResolver;
use Apha\Scheduling\EventScheduler;
use Apha\Serializer\JsonSerializer;
use Psr\Log\LoggerInterface;

class ToDoSagaSerializer extends JsonSerializer
{
    /**
     * @var EventScheduler
     */
    private $scheduler;

    /**
     * @var ParameterResolver
     */
    private $parameterResolver;

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
        $this->scheduler = $scheduler;
        $this->parameterResolver = $parameterResolver;
        $this->logger = $logger;
        parent::__construct();
    }

    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function deserialize(string $data, string $type)
    {
        /* @var $saga ToDoSaga */
        $saga = parent::deserialize($data, $type);
        $saga->setParameterResolver($this->parameterResolver);
        $saga->setEventScheduler($this->scheduler);
        $saga->setLogger($this->logger);
        return $saga;
    }
}