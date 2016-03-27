<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Scheduling\EventScheduler;
use Apha\Serializer\JsonSerializer;

class ToDoSagaSerializer extends JsonSerializer
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
        $saga->setEventScheduler($this->scheduler);
        return $saga;
    }
}