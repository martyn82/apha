<?php
declare(strict_types = 1);

namespace Apha\Scheduling;

use Apha\EventHandling\EventBus;
use Apha\Message\Event;

class SimpleEventScheduler implements EventScheduler
{
    /**
     * @var array
     */
    private $schedule;

    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * The scheduler uses ticks to determine whether or not to fire the event. Make sure to add the proper ticks
     * interval declaration in the client of the scheduler.
     * @param EventBus $eventBus
     */
    public function __construct(EventBus $eventBus)
    {
        register_tick_function([$this, 'onTick']);

        $this->schedule = [];
        $this->eventBus = $eventBus;
    }

    /**
     */
    public function __destruct()
    {
        unregister_tick_function([$this, 'onTick']);
    }

    /**
     */
    final public function onTick()
    {
        foreach ($this->schedule as $id => $task) {
            /* @var $task array */
            if (key($task) < time()) {
                $event = reset($task);
                unset($this->schedule[$id]);
                $this->eventBus->publish($event);
            }
        }
    }

    /**
     * @param ScheduleToken $token
     * @return void
     */
    public function cancelSchedule(ScheduleToken $token)
    {
        if (!array_key_exists($token->getTokenId(), $this->schedule)) {
            return;
        }

        unset($this->schedule[$token->getTokenId()]);
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @param Event $event
     * @return ScheduleToken
     */
    public function scheduleAt(\DateTimeInterface $dateTime, Event $event): ScheduleToken
    {
        $token = new ScheduleToken(uniqid());
        $this->schedule[$token->getTokenId()] = [
            $dateTime->getTimestamp() => $event
        ];

        return $token;
    }

    /**
     * @param \DateInterval $timeout
     * @param Event $event
     * @return ScheduleToken
     */
    public function scheduleAfter(\DateInterval $timeout, Event $event): ScheduleToken
    {
        $now = new \DateTime();
        $now->add($timeout);

        return $this->scheduleAt($now, $event);
    }
}