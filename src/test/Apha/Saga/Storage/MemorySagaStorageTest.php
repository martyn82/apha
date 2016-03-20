<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

use Apha\Domain\Identity;
use Apha\EventStore\EventDescriptor;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;

class MemorySagaStorageTest extends \PHPUnit_Framework_TestCase
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

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([new AssociationValue('foo', 'bar')])])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues())
        );

        $foundSagas = $storage->find(get_class($saga), $associationValues);

        self::assertCount(1, $foundSagas);
        self::assertEquals($saga->getId()->getValue(), $foundSagas[0]['identity']);
    }

    /**
     * @test
     */
    public function findRetrievesSagaByType()
    {
        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues())
        );

        $foundSagas = $storage->find(get_class($saga), []);

        self::assertCount(1, $foundSagas);
        self::assertEquals($saga->getId()->getValue(), $foundSagas[0]['identity']);
    }

    /**
     * @test
     */
    public function findRetrievesSagaByAssociationValues()
    {
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
            $this->associationValuesToArray($saga->getAssociationValues())
        );

        $foundSagas = $storage->find(get_class($saga), $associationValues);

        self::assertCount(1, $foundSagas);
        self::assertEquals($saga->getId()->getValue(), $foundSagas[0]['identity']);
    }

    /**
     * @test
     */
    public function findReturnsEmptyIfNoAssociationsAreFound()
    {
        $sagaIdentity = Identity::createNew();
        $associationValues = ['foo' => 'bar', 'bar' => 'baz'];

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues())
        );

        $foundSagas = $storage->find(get_class($saga), $associationValues);
        self::assertEmpty($foundSagas);
    }

    /**
     * @test
     */
    public function findByIdRetrievesSagaById()
    {
        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues())
        );

        $foundSaga = $storage->findById($sagaIdentity->getValue());
        self::assertEquals($saga->getId()->getValue(), $foundSaga['identity']);
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
    public function updateUpdatesSagaWithEvents()
    {
        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues())
        );

        $events = [
            EventDescriptor::record($sagaIdentity->getValue(), get_class($saga), 'SomethingHappened', '{}', 1)
        ];

        $expectedEvents = array_map(
            function (EventDescriptor $event) {
                return $event->toArray();
            },
            $events
        );

        $storage->update(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            $events
        );

        $foundSaga = $storage->findById($sagaIdentity->getValue());
        self::assertEquals($sagaIdentity->getValue(), $foundSaga['identity']);
        self::assertEquals($expectedEvents, $foundSaga['events']);
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

        $storage = new MemorySagaStorage();
        $storage->update(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues()),
            []
        );

        $foundSaga = $storage->findById($sagaIdentity->getValue());
        self::assertEquals($sagaIdentity->getValue(), $foundSaga['identity']);
    }

    /**
     * @test
     */
    public function deleteRemovesSaga()
    {
        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([new AssociationValue('foo', 'bar')])])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(
            get_class($saga),
            $saga->getId()->getValue(),
            $this->associationValuesToArray($saga->getAssociationValues())
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