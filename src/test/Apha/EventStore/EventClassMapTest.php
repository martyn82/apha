<?php
declare(strict_types=1);

namespace Apha\EventStore;

use Apha\Message\Event;

class EventClassMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function constructWithMappingAddsToMap()
    {
        $map = new EventClassMap([
            EventClassMapTest_Event1::class,
            EventClassMapTest_Event2::class
        ]);

        self::assertEquals(EventClassMapTest_Event1::class, $map->getClassByName('EventClassMapTest_Event1'));
        self::assertEquals(EventClassMapTest_Event2::class, $map->getClassByName('EventClassMapTest_Event2'));
    }

    /**
     * @test
     * @expectedException \OutOfBoundsException
     */
    public function getClassByNameThrowsExceptionIfEventNotInMap()
    {
        $map = new EventClassMap([]);
        $map->getClassByName('foo');
    }
}

class EventClassMapTest_Event1 extends Event {}
class EventClassMapTest_Event2 extends Event {}