<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

use Apha\Domain\Identity;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;
use Apha\Serializer\JsonSerializer;

/**
 * @group saga
 */
class MemorySagaStorageTest extends \PHPUnit_Framework_TestCase implements SagaStorageTest
{
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

        $storage = new MemorySagaStorage();
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

        $storage = new MemorySagaStorage();
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

        $storage = new MemorySagaStorage();
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

        $storage = new MemorySagaStorage();
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

        $storage = new MemorySagaStorage();
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
        $storage = new MemorySagaStorage();

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

        $storage = new MemorySagaStorage();
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

        $storage = new MemorySagaStorage();
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

        $storage = new MemorySagaStorage();
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

        $storage = new MemorySagaStorage();
        $storage->delete($sagaIdentity->getValue());
    }
}