<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\ToDo;

use Apha\Domain\Identity;
use Apha\EventHandling\TypedEventHandler;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;
use Apha\Scheduling\EventScheduler;
use JMS\Serializer\Annotation as Serializer;

class ToDoSaga extends Saga
{
    use TypedEventHandler;

    /**
     * @var EventScheduler
     */
    private $scheduler;

    /**
     * @Serializer\Type("array")
     * @var array
     */
    private $openItems;

    /**
     * @Serializer\Type("boolean")
     * @var bool
     */
    private $active;

    /**
     * @param Identity $identity
     * @param AssociationValues $associationValues
     */
    public function __construct(Identity $identity, AssociationValues $associationValues)
    {
        parent::__construct($identity, $associationValues);
        $this->openItems = [];
        $this->active = false;
    }

    /**
     * @param EventScheduler $eventScheduler
     */
    public function setEventScheduler(EventScheduler $eventScheduler)
    {
        $this->scheduler = $eventScheduler;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param ToDoItemCreated $event
     */
    public function onToDoItemCreated(ToDoItemCreated $event)
    {
        if ($this->scheduler != null) {
            $token = $this->scheduler->scheduleAfter(
                new \DateInterval("PT{$event->getExpireSeconds()}S"),
                new DeadlineExpired($event->getIdentity())
            );
        } else {
            $token = null;
        }

        $this->openItems[$event->getIdentity()->getValue()] = [
            'description' => $event->getDescription(),
            'token' => $token
        ];

        $this->active = true;
    }

    /**
     * @param ToDoItemDone $event
     */
    public function onToDoItemDone(ToDoItemDone $event)
    {
        $token = $this->openItems[$event->getIdentity()->getValue()]['token'];

        if ($token != null && $this->scheduler != null) {
            $this->scheduler->cancelSchedule($token);
        }

        unset($this->openItems[$event->getIdentity()->getValue()]);

        if (empty($this->openItems)) {
            $this->active = false;
        }
    }

    /**
     * @param DeadlineExpired $event
     */
    public function onDeadlineExpired(DeadlineExpired $event)
    {
        unset($this->openItems[$event->getIdentity()->getValue()]);
    }
}
