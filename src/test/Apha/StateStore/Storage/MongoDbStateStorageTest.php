<?php
declare(strict_types = 1);

namespace Apha\StateStore\Storage;

use Apha\Message\Event;
use Apha\StateStore\Document;
use MongoDB\Collection;
use MongoDB\Driver\Manager;
use JMS\Serializer\Annotation as Serializer;

/**
 * @group statestore
 * @group mongodb
 */
class MongoDbStateStorageTest extends \PHPUnit_Framework_TestCase implements StateStorageTest
{
    /**
     * @var string
     */
    private static $identityField = 'id';

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
     * @test
     */
    public function upsertAddsDocumentToStorage()
    {
        $identity = 'identity';
        $document = new MongoDbStateStorageTest_Document($identity, 1);

        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->upsert($identity, $document);

        self::assertEquals($document, $storage->find($identity));
    }

    /**
     * @test
     */
    public function upsertUpdatesExistingDocument()
    {
        $identity = 'identity';
        $document1 = new MongoDbStateStorageTest_Document($identity, 1);
        $document2 = new MongoDbStateStorageTest_Document($identity, 2);

        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->upsert($identity, $document1);
        $storage->upsert($identity, $document2);

        self::assertEquals($document2, $storage->find($identity));
    }

    /**
     * @test
     * @expectedException \Apha\StateStore\Storage\DocumentNotFoundException
     */
    public function deleteRemovesDocumentFromStorage()
    {
        $identity = 'identity';
        $document = new MongoDbStateStorageTest_Document($identity, 1);

        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->upsert($identity, $document);

        $storage->delete($identity);
        $storage->find($identity);
    }

    /**
     * @test
     */
    public function deleteIsIdempotent()
    {
        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->delete('nonexistent');
    }

    /**
     * @test
     */
    public function findRetrievesDocumentByIdentity()
    {
        $identity = 'identity';
        $document = new MongoDbStateStorageTest_Document($identity, 1);

        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->upsert($identity, $document);

        $result = $storage->find($identity);
        self::assertEquals($document, $result);
    }

    /**
     * @test
     * @expectedException \Apha\StateStore\Storage\DocumentNotFoundException
     */
    public function findThrowsExceptionIfDocumentCannotBeFound()
    {
        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->find('nonexistent');
    }

    /**
     * @test
     */
    public function clearRemovesAllDocumentsFromStorage()
    {
        $identity = 'identity';
        $document = new MongoDbStateStorageTest_Document($identity, 1);

        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->upsert($identity, $document);

        $storage->clear();

        $all = $storage->findAll();
        self::assertEmpty($all);
    }

    /**
     * @test
     */
    public function clearIsIdempotent()
    {
        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->clear();

        $all = $storage->findAll();
        self::assertEmpty($all);
    }

    /**
     * @test
     */
    public function findAllRetrievesAPageOfDocuments()
    {
        $document1 = new MongoDbStateStorageTest_Document('1', 1);
        $document2 = new MongoDbStateStorageTest_Document('2', 1);
        $document3 = new MongoDbStateStorageTest_Document('3', 1);

        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);

        $storage->upsert('1', $document1);
        $storage->upsert('2', $document2);
        $storage->upsert('3', $document3);

        $page = $storage->findAll(0, 1);
        self::assertCount(1, $page);
        self::assertEquals($document1, $page[0]);

        $page = $storage->findAll(1, 1);
        self::assertCount(1, $page);
        self::assertEquals($document2, $page[0]);
    }

    /**
     * @test
     */
    public function findAllRetrievesEmptyArrayIfNoneFound()
    {
        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $all = $storage->findAll();

        self::assertEmpty($all);
    }

    /**
     * @test
     */
    public function findByRetrievesAPageOfDocumentsMatchingCriteria()
    {
        $document = new MongoDbStateStorageTest_Document('id1', 1);

        $storage = new MongoDbStateStorage(self::$collection, self::$identityField, MongoDbStateStorageTest_Document::class);
        $storage->upsert('id1', $document);

        $page = $storage->findBy(['foo' => 'foo']);
        self::assertCount(1, $page);
        self::assertEquals($document, $page[0]);
    }
}

class MongoDbStateStorageTest_Document implements Document
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $id;

    /**
     * @Serializer\Type("integer")
     * @var int
     */
    private $version;

    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $foo = 'foo';

    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $bar = 'bar';

    /**
     * @param string $id
     * @param int $version
     */
    public function __construct(string $id, int $version)
    {
        $this->id = $id;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param Event $event
     * @return void
     */
    public function apply(Event $event)
    {
        $this->version++;
    }
}