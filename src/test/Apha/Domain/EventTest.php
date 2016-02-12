<?php

namespace Apha\Domain;

class EventTest extends \PHPUnit_Framework_TestCase
{
    public function testNameReturnsTheBaseClassName()
    {
        self::assertEquals('EventTest_FakeEvent', EventTest_FakeEvent::getName());

        $event = new EventTest_FakeEvent();
        self::assertEquals('EventTest_FakeEvent', $event->getEventName());
    }
}

class EventTest_FakeEvent extends Event {}