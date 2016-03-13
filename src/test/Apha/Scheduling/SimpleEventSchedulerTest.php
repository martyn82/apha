<?php
declare(ticks = 1);
declare(strict_types = 1);

namespace Apha\Scheduling;

use Apha\EventHandling\EventBus;
use Apha\Message\Event;

class SimpleEventSchedulerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return EventBus
     */
    private function createEventBus(): EventBus
    {
        return $this->getMockBuilder(EventBus::class)
            ->getMock();
    }

    /**
     * @param int $seconds
     */
    private function wait(int $seconds)
    {
        $passed = 0;
        $startTime = time();

        while ($passed < $seconds) {
            $passed = time() - $startTime;
        }
    }

    /**
     * @test
     */
    public function cancelScheduleCancelsAPreviouslyScheduledEvent()
    {
        $eventBus = $this->createEventBus();

        $eventBus->expects(self::never())
            ->method('publish');

        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        // schedule for execution 1 second from now
        $now = new \DateTimeImmutable();
        $time = $now->add(new \DateInterval('PT1S'));

        $scheduler = new SimpleEventScheduler($eventBus);
        $token = $scheduler->scheduleAt($time, $event);

        $scheduler->cancelSchedule($token);

        $this->wait(2);
    }

    /**
     * @test
     */
    public function cancelScheduleOfAnAlreadyExecutedEventIsOk()
    {
        $eventBus = $this->createEventBus();

        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        // schedule for execution 1 second from now
        $now = new \DateTimeImmutable();
        $time = $now->add(new \DateInterval('PT1S'));

        $scheduler = new SimpleEventScheduler($eventBus);
        $token = $scheduler->scheduleAt($time, $event);

        $this->wait(2);

        $scheduler->cancelSchedule($token);
    }

    /**
     * @test
     */
    public function scheduleAtSchedulesEventAtGivenTime()
    {
        $eventBus = $this->createEventBus();

        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $eventBus->expects(self::once())
            ->method('publish')
            ->with($event);

        // schedule for execution 1 second from now
        $now = new \DateTimeImmutable();
        $time = $now->add(new \DateInterval('PT1S'));

        $scheduler = new SimpleEventScheduler($eventBus);
        $scheduler->scheduleAt($time, $event);

        $this->wait(2);
    }

    /**
     * @test
     */
    public function scheduleAtWithPassedTimeSchedulesEventImmediately()
    {
        $eventBus = $this->createEventBus();

        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $eventBus->expects(self::once())
            ->method('publish')
            ->with($event);

        // schedule for execution 1 day ago
        $now = new \DateTimeImmutable();
        $time = $now->sub(new \DateInterval('P1D'));

        $scheduler = new SimpleEventScheduler($eventBus);
        $scheduler->scheduleAt($time, $event);

        $this->wait(1);
    }


    /**
     * @test
     */
    public function scheduleAfterSchedulesEventAfterTimePeriod()
    {
        $eventBus = $this->createEventBus();

        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $eventBus->expects(self::once())
            ->method('publish')
            ->with($event);

        $in1Second = new \DateInterval('PT1S');

        $scheduler = new SimpleEventScheduler($eventBus);
        $scheduler->scheduleAfter($in1Second, $event);

        $this->wait(2);
    }
}