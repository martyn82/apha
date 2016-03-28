<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

use Apha\Domain\Identity;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;
use Apha\Serializer\JsonSerializer;

abstract class SagaStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return SagaStorage
     */
    abstract protected function createSagaStorage(): SagaStorage;

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

        $storage = $this->createSagaStorage();
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
    public function findRetrievesSagaByTypeAndAssociationValues()
    {
        $serializer = new JsonSerializer();
        $sagaIdentity = Identity::createNew();

        $saga1 = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([
                $sagaIdentity,
                new AssociationValues([
                    new AssociationValue('foo', 'bar')
                ])
            ])
            ->setMockClassName('SomeSaga')
            ->getMock();

        $saga2 = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([
                Identity::createNew(),
                new AssociationValues([
                    new AssociationValue('foo', 'baz')
                ])
            ])
            ->setMockClassName('SomeSaga')
            ->getMock();

        $storage = $this->createSagaStorage();

        $storage->insert(
            'SomeSaga',
            $saga1->getId()->getValue(),
            $this->associationValuesToArray($saga1->getAssociationValues()),
            $serializer->serialize($saga1)
        );

        $storage->insert(
            'SomeSaga',
            $saga2->getId()->getValue(),
            $this->associationValuesToArray($saga2->getAssociationValues()),
            $serializer->serialize($saga2)
        );

        $foundSagas = $storage->find('SomeSaga', $this->associationValuesToArray($saga1->getAssociationValues()));

        self::assertCount(1, $foundSagas);
        self::assertEquals($saga1->getId()->getValue(), $foundSagas[0]);
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

        $storage = $this->createSagaStorage();
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

        $storage = $this->createSagaStorage();
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
        $storage = $this->createSagaStorage();

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

        $storage = $this->createSagaStorage();
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

        $storage = $this->createSagaStorage();
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

        $storage = $this->createSagaStorage();
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

        $storage = $this->createSagaStorage();
        $storage->delete($sagaIdentity->getValue());
    }
}
