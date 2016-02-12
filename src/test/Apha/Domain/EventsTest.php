<?php

namespace Apha\Domain;

class EventsTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructAcceptsArrayWithEvents()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $events = [
            $event
        ];

        $instance = new Events($events);
        self::assertCount(count($events), $instance->getIterator());
    }

    public function testConstructRaisesErrorIfNotAllElementsAreEvent()
    {
        self::setExpectedException('TypeError');

        $events = [
            new \stdClass()
        ];

        new Events($events);
    }

    public function testAddAddsEventToList()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $instance = new Events();
        $instance->add($event);

        self::assertCount(1, $instance->getIterator());
    }

    public function testClearMakesListEmpty()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $instance = new Events([$event]);
        $instance->clear();

        self::assertCount(0, $instance->getIterator());
    }

    public function testSizeReturnsNumberOfEvents()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $instance = new Events([$event]);

        self::assertEquals(1, $instance->size());
    }
}