<?php
declare(strict_types = 1);

namespace Apha\Scheduling;

use Apha\Message\Event;

interface EventScheduler
{
    /**
     * @param ScheduleToken $token
     * @return void
     */
    public function cancelSchedule(ScheduleToken $token);

    /**
     * @param \DateTimeInterface $dateTime
     * @param Event $event
     * @return ScheduleToken
     */
    public function scheduleAt(\DateTimeInterface $dateTime, Event $event): ScheduleToken;

    /**
     * @param \DateInterval $timeout
     * @param Event $event
     * @return ScheduleToken
     */
    public function scheduleAfter(\DateInterval $timeout, Event $event): ScheduleToken;
}
