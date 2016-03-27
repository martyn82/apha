<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Annotations\Annotation\EndSaga;
use Apha\Annotations\Annotation\SagaEventHandler;
use Apha\Annotations\Annotation\StartSaga;
use Apha\Domain\Identity;
use Apha\Examples\Domain\ToDo\DeadlineExpired;
use Apha\Examples\Domain\ToDo\ToDoItemCreated;
use Apha\Examples\Domain\ToDo\ToDoItemDone;
use Apha\Saga\Annotation\AnnotatedSaga;
use Apha\Scheduling\EventScheduler;
use JMS\Serializer\Annotation as Serializer;

class ToDoSaga extends AnnotatedSaga
{
    /**
     * @Serializer\Type("array<string, Apha\Scheduling\ScheduleToken>")
     * @var array
     */
    private $openItems;

    /**
     * @Serializer\Exclude()
     * @var EventScheduler
     */
    private $scheduler;

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        parent::__construct($identity);
        $this->openItems = [];
    }

    /**
     * @param EventScheduler $scheduler
     */
    public function setEventScheduler(EventScheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    /**
     * @StartSaga()
     * @SagaEventHandler(associationProperty = "identity")
     * @param ToDoItemCreated $event
     */
    public function onToDoItemCreated(ToDoItemCreated $event)
    {
        $token = $this->scheduler->scheduleAfter(
            new \DateInterval("PT{$event->getExpireSeconds()}S"),
            new DeadlineExpired($event->getIdentity())
        );

        $this->openItems[$event->getIdentity()->getValue()] = $token;
    }

    /**
     * @SagaEventHandler(associationProperty = "identity")
     * @param ToDoItemDone $event
     */
    public function onToDoItemDone(ToDoItemDone $event)
    {
        $token = $this->openItems[$event->getIdentity()->getValue()];

        if ($token != null) {
            $this->scheduler->cancelSchedule($token);
        }

        unset($this->openItems[$event->getIdentity()->getValue()]);
    }

    /**
     * @EndSaga()
     * @SagaEventHandler(associationProperty = "identity")
     * @param DeadlineExpired $event
     */
    public function onDeadlineExpired(DeadlineExpired $event)
    {
        unset($this->openItems[$event->getIdentity()->getValue()]);
    }
}