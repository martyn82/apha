<?php
declare(strict_types = 1);

namespace Apha\EventStore\Storage;

use Apha\EventStore\EventDescriptor;
use MongoDB\Collection;
use MongoDB\Driver\Manager;

/**
 * @group eventstore
 * @group mongodb
 */
class MongoDbEventStorageTest extends \PHPUnit_Framework_TestCase implements EventStorageTest
{
    /**
     * @var Manager
     */
    private static $manager;

    /**
     * @var Collection
     */
    private static $collection;

    /**
     * @var string
     */
    private static $testDb;

    /**
     * @var string
     */
    private static $testCollection;

    /**
     */
    public static function setUpBeforeClass()
    {
        if (!class_exists('MongoDB\\Driver\\Manager')) {
            self::markTestSkipped("MongoDB library not found.");
        }

        self::$testDb = uniqid('test_');
        self::$testCollection = uniqid('test_');

        self::$manager = new Manager(
            'mongodb://localhost:27017'
        );
    }

    /**
     */
    public static function tearDownAfterClass()
    {
        if (self::$collection == null) {
            return;
        }

        try {
            self::$collection->drop(['dropDatabase' => 1]);
        } catch (\Exception $e) {
        }
    }

    protected function setUp()
    {
        if (!class_exists('MongoDB\\Collection')) {
            self::markTestSkipped("MongoDB library not found.");
        }

        try {
            self::$manager->selectServer(self::$manager->getReadPreference());
        } catch (\Exception $e) {
            self::markTestSkipped($e->getMessage());
        }

        self::$collection = new Collection(self::$manager, self::$testDb, self::$testCollection);
    }

    protected function tearDown()
    {
        self::$collection->drop();
    }

    /**
     * @return Collection
     */
    private function getCollection()
    {
        return self::$collection;
    }

    /**
     * @test
     */
    public function containsReturnsFalseIfIdentityDoesNotExistInStorage()
    {
        $identity = 'foo';
        $identityField = 'identity';

        $collection = $this->getCollection();

        $storage = new MongoDbEventStorage($collection, $identityField);
        self::assertFalse($storage->contains($identity));
    }

    /**
     * @test
     */
    public function containsReturnsTrueIfIdentityExistsInStorage()
    {
        $identity = 'foo';
        $identityField = 'identity';

        $collection = $this->getCollection();

        $event = EventDescriptor::record(
            $identity,
            'aggregateType',
            'someevent',
            '{}',
            1
        );

        $storage = new MongoDbEventStorage($collection, $identityField);
        $storage->append($event);

        self::assertTrue($storage->contains($identity));
    }

    /**
     * @test
     */
    public function appendAcceptsTheFirstEventForAnIdentity()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
        $identity = 'someidentity';
        $event = EventDescriptor::record(
            $identity,
            'aggregateType',
            'someevent',
            '{}',
            1
        );

        $storage = new MongoDbEventStorage($collection, $identityField);
        self::assertTrue($storage->append($event));
    }

    /**
     * @test
     */
    public function appendAcceptsTheNextEventForAnIdentity()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
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

        $storage = new MongoDbEventStorage($collection, $identityField);

        self::assertTrue($storage->append($event1));
        self::assertTrue($storage->append($event2));
    }

    /**
     * @test
     */
    public function findWillReturnEmptyResultIfIdentityDoesNotExist()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
        $identity  = 'identity';

        $storage = new MongoDbEventStorage($collection, $identityField);
        self::assertEmpty($storage->find($identity));
    }

    /**
     * @test
     */
    public function findWillReturnAllEventsInOrderForIdentity()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
        $identity  = 'identity';

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

        $storage = new MongoDbEventStorage($collection, $identityField);
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
        $collection = $this->getCollection();

        $identityField = 'identity';
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

        $storage = new MongoDbEventStorage($collection, $identityField);
        $storage->append($event1);
        $storage->append($event2);

        $identities = $storage->findIdentities();

        self::assertCount(2, $identities);
        self::assertContains($identity1, $identities);
        self::assertContains($identity2, $identities);
    }
}