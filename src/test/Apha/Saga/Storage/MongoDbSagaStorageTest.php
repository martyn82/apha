<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

use MongoDB\Collection;
use MongoDB\Driver\Manager;

/**
 * @group saga
 * @group mongodb
 */
class MongoDbSagaStorageTest extends SagaStorageTest
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
     * @return SagaStorage
     */
    protected function createSagaStorage(): SagaStorage
    {
        return new MongoDbSagaStorage(self::$collection);
    }
}
