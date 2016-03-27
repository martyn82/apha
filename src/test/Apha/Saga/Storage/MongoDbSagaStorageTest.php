<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

use Apha\Domain\Identity;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;
use Apha\Serializer\JsonSerializer;
use MongoDB\Collection;
use MongoDB\Driver\Manager;

/**
 * @group saga
 * @group mongodb
 */
class MongoDbSagaStorageTest extends \PHPUnit_Framework_TestCase implements SagaStorageTest
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
     * @param AssociationValues $associationValues
     * @return array
     */
    private function associationValuesToArray(AssociationValues $associationValues): array
    {
        return array_reduce(
            $associationValues->getArrayCopy(),
            function (array $accumulator, AssociationValue $item): array {
                $accumulator[$item->getKey()] = $item->getValue();
                return $accumulator;
            },
            []
        );
    }

    /**
     * @test
     */
    public function insertRegistersSaga()
    {
        $sagaIdentity = Identity::createNew();
        $associationValues = ['foo' => 'bar'];
        $serializer = new JsonSerializer();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([new AssociationValue('foo', 'bar')])])
            ->getMock();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );

        $foundSagas = $storage->find(get_class($saga), $associationValues);

        self::assertCount(1, $foundSagas);
        self::assertEquals($saga->getId()->getValue(), $foundSagas[0]);
    }

    /**
     * @test
     */
    public function findRetrievesSagaByType()
    {
        $sagaIdentity = Identity::createNew();
        $serializer = new JsonSerializer();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );

        $foundSagas = $storage->find(get_class($saga), []);

        self::assertCount(1, $foundSagas);
        self::assertEquals($saga->getId()->getValue(), $foundSagas[0]);
    }

    /**
     * @test
     */
    public function findRetrievesSagaByAssociationValues()
    {
        $serializer = new JsonSerializer();
        $sagaIdentity = Identity::createNew();
        $associationValues = ['foo' => 'bar', 'bar' => 'baz'];

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([
                $sagaIdentity,
                new AssociationValues([
                    new AssociationValue('foo', 'bar'),
                    new AssociationValue('bar', 'baz')
                ])
            ])
            ->getMock();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );

        $foundSagas = $storage->find(get_class($saga), $associationValues);

        self::assertCount(1, $foundSagas);
        self::assertEquals($saga->getId()->getValue(), $foundSagas[0]);
    }

    /**
     * @test
     */
    public function findReturnsEmptyIfNoAssociationsAreFound()
    {
        $serializer = new JsonSerializer();
        $sagaIdentity = Identity::createNew();
        $associationValues = ['foo' => 'bar', 'bar' => 'baz'];

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );

        $foundSagas = $storage->find(get_class($saga), $associationValues);
        self::assertEmpty($foundSagas);
    }

    /**
     * @test
     */
    public function findByIdRetrievesSagaById()
    {
        $serializer = new JsonSerializer();
        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );

        $foundSaga = $storage->findById($sagaIdentity->getValue());
        self::assertEquals($serializer->serialize($saga), $foundSaga);
    }

    /**
     * @test
     */
    public function findByIdRetrievesEmptyResultIfSagaDoesNotExist()
    {
        $sagaIdentity = Identity::createNew();
        $storage = new MongoDbSagaStorage(self::$collection);

        $foundSaga = $storage->findById($sagaIdentity->getValue());
        self::assertEmpty($foundSaga);
    }

    /**
     * @test
     */
    public function updateUpdatesSaga()
    {
        $serializer = new JsonSerializer();
        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );

        $serializer = new JsonSerializer();

        $storage->update(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );

        $foundSaga = $storage->findById($sagaIdentity->getValue());
        self::assertEquals($serializer->serialize($saga), $foundSaga);
    }

    /**
     * @test
     */
    public function updateInsertsSagaIfItDoesNotExist()
    {
        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $serializer = new JsonSerializer();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->update(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );

        $foundSaga = $storage->findById($sagaIdentity->getValue());
        self::assertEquals($serializer->serialize($saga), $foundSaga);
    }

    /**
     * @test
     */
    public function deleteRemovesSaga()
    {
        $serializer = new JsonSerializer();
        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([new AssociationValue('foo', 'bar')])])
            ->getMock();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $serializer->serialize($saga)
        );
        $storage->delete($sagaIdentity->getValue());

        $foundSaga = $storage->findById($sagaIdentity->getValue());
        self::assertEmpty($foundSaga);
    }

    /**
     * @test
     */
    public function deleteIsIdempotent()
    {
        $sagaIdentity = Identity::createNew();

        $storage = new MongoDbSagaStorage(self::$collection);
        $storage->delete($sagaIdentity->getValue());
    }
}
