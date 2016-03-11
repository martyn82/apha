<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

use Apha\Domain\Identity;
use Apha\EventStore\EventDescriptor;
use Apha\Saga\Saga;

class MemorySagaStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function insertRegistersSaga()
    {
        $sagaIdentity = Identity::createNew();
        $associationValues = ['foo' => 'bar'];

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, $associationValues])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues());

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
            ->setConstructorArgs([$sagaIdentity, []])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues());

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
            ->setConstructorArgs([$sagaIdentity, $associationValues])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues());

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
            ->setConstructorArgs([$sagaIdentity, []])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues());

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
            ->setConstructorArgs([$sagaIdentity, []])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues());

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
            ->setConstructorArgs([$sagaIdentity, []])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues());

        $events = [
            EventDescriptor::record($sagaIdentity->getValue(), get_class($saga), 'SomethingHappened', '{}', 1)
        ];

        $expectedEvents = array_map(
            function (EventDescriptor $event) {
                return $event->toArray();
            },
            $events
        );

        $storage->update(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues(), $events);

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
            ->setConstructorArgs([$sagaIdentity, []])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->update(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues(), []);

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
            ->setConstructorArgs([$sagaIdentity, ['foo' => 'bar']])
            ->getMock();

        $storage = new MemorySagaStorage();
        $storage->insert(get_class($saga), $saga->getId()->getValue(), $saga->getAssociationValues());
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