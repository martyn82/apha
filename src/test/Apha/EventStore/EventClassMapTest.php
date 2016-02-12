<?php

namespace Apha\EventStore;

use Apha\Domain\Message\Event;

class EventClassMapTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructWithMappingAddsToMap()
    {
        $map = new EventClassMap([
            EventClassMapTest_Event1::class,
            EventClassMapTest_Event2::class
        ]);

        self::assertEquals(EventClassMapTest_Event1::class, $map->getClassByName('EventClassMapTest_Event1'));
        self::assertEquals(EventClassMapTest_Event2::class, $map->getClassByName('EventClassMapTest_Event2'));
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function testGetClassByNameThrowsExceptionIfEventNotInMap()
    {
        $map = new EventClassMap([]);
        $map->getClassByName('foo');
    }
}

class EventClassMapTest_Event1 extends Event {}
class EventClassMapTest_Event2 extends Event {}