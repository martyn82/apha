<?php
declare(strict_types=1);

namespace Apha\Domain\Message;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getNameReturnsTheEventName()
    {
        self::assertEquals('EventTest_FakeEvent', EventTest_FakeEvent::getName());

        $event = new EventTest_FakeEvent();
        self::assertEquals('EventTest_FakeEvent', $event->getEventName());
    }
}

class EventTest_FakeEvent extends Event {}