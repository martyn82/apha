<?php
declare(strict_types = 1);

namespace Apha\Message;

/**
 * @group message
 */
class EventsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function constructAcceptsArrayWithEvents()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $events = [
            $event
        ];

        $instance = new Events($events);
        self::assertCount(count($events), $instance->getIterator());
    }

    /**
     * @test
     * @expectedException \TypeError
     */
    public function constructRaisesErrorIfNotAllElementsAreEvent()
    {
        $events = [
            new \stdClass()
        ];

        new Events($events);
    }

    /**
     * @test
     */
    public function addAddsEventToList()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $instance = new Events();
        $instance->add($event);

        self::assertCount(1, $instance->getIterator());
    }

    /**
     * @test
     */
    public function clearMakesListEmpty()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $instance = new Events([$event]);
        $instance->clear();

        self::assertCount(0, $instance->getIterator());
    }

    /**
     * @test
     */
    public function sizeReturnsNumberOfEvents()
    {
        $event = $this->getMockBuilder(Event::class)
            ->getMock();

        $instance = new Events([$event]);

        self::assertEquals(1, $instance->size());
    }
}