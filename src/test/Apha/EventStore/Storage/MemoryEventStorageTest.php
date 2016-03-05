<?php
declare(strict_types = 1);

namespace Apha\EventStore\Storage;

use Apha\EventStore\EventDescriptor;

class MemoryEventStorageTest extends \PHPUnit_Framework_TestCase implements EventStorageTest
{
    /**
     * @test
     */
    public function containsReturnsFalseIfIdentityDoesNotExistInStorage()
    {
        $identity = 'nonexistent';
        $storage = new MemoryEventStorage();
        self::assertFalse($storage->contains($identity));
    }

    /**
     * @test
     */
    public function containsReturnsTrueIfIdentityExistsInStorage()
    {
        $identity = 'existing';
        $event = EventDescriptor::record(
            $identity,
            'aggregateType',
            'someevent',
            '{}',
            1
        );

        $storage = new MemoryEventStorage();
        $storage->append($event);

        self::assertTrue($storage->contains($identity));
    }

    /**
     * @test
     */
    public function appendAcceptsTheFirstEventForAnIdentity()
    {
        $identity = 'someidentity';
        $event = EventDescriptor::record(
            $identity,
            'aggregateType',
            'someevent',
            '{}',
            1
        );

        $storage = new MemoryEventStorage();
        self::assertTrue($storage->append($event));
    }

    /**
     * @test
     */
    public function appendAcceptsTheNextEventForAnIdentity()
    {
        $identity = 'someidentity';

        $event1 = EventDescriptor::record(
            $identity,
            'aggregateType',
            'someevent',
            '{}',
            1
        );

        $event2 = EventDescriptor::record(
            $identity,
            'aggregateType',
            'someevent',
            '{}',
            2
        );

        $storage = new MemoryEventStorage();

        self::assertTrue($storage->append($event1));
        self::assertTrue($storage->append($event2));
    }

    /**
     * @test
     */
    public function findWillReturnEmptyResultIfIdentityDoesNotExist()
    {
        $storage = new MemoryEventStorage();
        self::assertEmpty($storage->find('nonexistent'));
    }

    /**
     * @test
     */
    public function findWillReturnAllEventsInOrderForIdentity()
    {
        $identity = 'someidentity';
        $event1 = EventDescriptor::record(
            $identity,
            'aggregateType',
            'someevent1',
            '{}',
            1
        );
        $event2 = EventDescriptor::record(
            $identity,
            'aggregateType',
            'someevent2',
            '{}',
            2
        );

        $storage = new MemoryEventStorage();
        $storage->append($event1);
        $storage->append($event2);

        $events = $storage->find($identity);

        self::assertCount(2, $events);
        self::assertEquals($event1, $events[0]);
        self::assertEquals($event2, $events[1]);
    }

    /**
     * @test
     */
    public function findIdentitiesWillRetrieveAllKnownIdentities()
    {
        $identity1 = 'someidentity';
        $identity2 = 'someotheridentity';

        $event1 = EventDescriptor::record(
            $identity1,
            'aggregateType',
            'someevent',
            '{}',
            1
        );
        $event2 = EventDescriptor::record(
            $identity2,
            'aggregateType',
            'someevent',
            '{}',
            1
        );

        $storage = new MemoryEventStorage();
        $storage->append($event1);
        $storage->append($event2);

        $identities = $storage->findIdentities();

        self::assertCount(2, $identities);
        self::assertContains($identity1, $identities);
        self::assertContains($identity2, $identities);
    }
}