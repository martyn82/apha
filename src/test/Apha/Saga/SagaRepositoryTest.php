<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\EventStore\EventDescriptor;
use Apha\Message\Event;
use Apha\Message\Events;
use Apha\Saga\Storage\SagaStorage;
use Apha\Serializer\JsonSerializer;
use Apha\Serializer\Serializer;

class SagaRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return SagaStorage
     */
    private function createStorage(): SagaStorage
    {
        return $this->getMockBuilder(SagaStorage::class)
            ->getMock();
    }

    /**
     * @return Serializer
     */
    private function createSerializer(): Serializer
    {
        return new JsonSerializer();
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
    public function addWillInsertSagaIntoStorage()
    {
        $storage = $this->createStorage();
        $serializer = $this->createSerializer();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([Identity::createNew(), new AssociationValues([])])
            ->getMock();

        $saga->expects(self::any())
            ->method('isActive')
            ->willReturn(true);

        $storage->expects(self::once())
            ->method('insert')
            ->with(
                get_class($saga),
                $saga->getId()->getValue(),
                $this->associationValuesToArray($saga->getAssociationValues())
            );

        $repository = new SagaRepository($storage, $serializer);
        $repository->add($saga);
    }

    /**
     * @test
     */
    public function addWillNotInsertIntoStorageIfSagaIsInactive()
    {
        $storage = $this->createStorage();
        $serializer = $this->createSerializer();

        $saga = $this->getMockBuilder(Saga::class)
            ->disableOriginalConstructor()
            ->getMock();

        $saga->expects(self::any())
            ->method('isActive')
            ->willReturn(false);

        $storage->expects(self::never())
            ->method('insert');

        $repository = new SagaRepository($storage, $serializer);
        $repository->add($saga);
    }

    /**
     * @test
     */
    public function commitUpdatesSagaInStorage()
    {
        $storage = $this->createStorage();
        $serializer = $this->createSerializer();

        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $event = new SagaRepositoryTest_Event();
        $events = new Events([
            $event
        ]);

        $eventDescriptors = [
            EventDescriptor::record(
                $sagaIdentity->getValue(),
                get_class($saga),
                $event->getName(),
                $serializer->serialize($event),
                $event->getVersion()
            )
        ];

        $saga->expects(self::any())
            ->method('isActive')
            ->willReturn(true);

        $saga->expects(self::any())
            ->method('getUncommittedChanges')
            ->willReturn($events);

        $storage->expects(self::once())
            ->method('update')
            ->with(
                get_class($saga),
                $saga->getId()->getValue(),
                $this->associationValuesToArray($saga->getAssociationValues()),
                $eventDescriptors
            );

        $repository = new SagaRepository($storage, $serializer);
        $repository->commit($saga);
    }

    /**
     * @test
     */
    public function commitDeletesSagaIfItIsInactive()
    {
        $storage = $this->createStorage();
        $serializer = $this->createSerializer();

        $sagaIdentity = Identity::createNew();

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, new AssociationValues([])])
            ->getMock();

        $saga->expects(self::any())
            ->method('isActive')
            ->willReturn(false);

        $storage->expects(self::once())
            ->method('delete')
            ->with($saga->getId()->getValue());

        $repository = new SagaRepository($storage, $serializer);
        $repository->commit($saga);
    }

    /**
     * @test
     */
    public function findReturnsIdentitiesForGivenSagaTypeAndAssociationValues()
    {
        $storage = $this->createStorage();
        $serializer = $this->createSerializer();

        $sagaIdentity = Identity::createNew();
        $associationValues = new AssociationValues([new AssociationValue('foo', 'bar')]);

        $saga = $this->getMockBuilder(Saga::class)
            ->setConstructorArgs([$sagaIdentity, $associationValues])
            ->getMock();

        $storage->expects(self::once())
            ->method('find')
            ->with(
                get_class($saga),
                $this->associationValuesToArray($saga->getAssociationValues())
            )
            ->willReturn([
                [
                    'type' => get_class($saga),
                    'identity' => $sagaIdentity->getValue(),
                    'associations' => $associationValues,
                    'events' => []
                ]
            ]);

        $repository = new SagaRepository($storage, $serializer);
        $identities = $repository->find(get_class($saga), new AssociationValue('foo', 'bar'));

        self::assertCount(1, $identities);
        self::assertEquals($sagaIdentity->getValue(), $identities[0]);
    }

    /**
     * @test
     */
    public function loadRetrievesASagaById()
    {
        $storage = $this->createStorage();
        $serializer = $this->createSerializer();

        $sagaIdentity = Identity::createNew();
        $saga = new SagaRepositoryTest_Saga($sagaIdentity, new AssociationValues([new AssociationValue('foo', 'bar')]));

        $storage->expects(self::once())
            ->method('findById')
            ->with($saga->getId()->getValue())
            ->willReturn([
                'type' => get_class($saga),
                'identity' => $sagaIdentity->getValue(),
                'associations' => $this->associationValuesToArray($saga->getAssociationValues()),
                'events' => []
            ]);

        $repository = new SagaRepository($storage, $serializer);

        $actual = $repository->load($sagaIdentity);
        self::assertEquals($saga->getId()->getValue(), $actual->getId()->getValue());
        self::assertEquals($saga->getAssociationValues(), $actual->getAssociationValues());
    }

    /**
     * @test
     */
    public function loadReturnsNullIfSagaNotFound()
    {
        $storage = $this->createStorage();
        $serializer = $this->createSerializer();

        $sagaIdentity = Identity::createNew();

        $storage->expects(self::once())
            ->method('findById')
            ->with($sagaIdentity->getValue())
            ->willReturn([]);

        $repository = new SagaRepository($storage, $serializer);

        $actual = $repository->load($sagaIdentity);
        self::assertNull($actual);
    }
}

class SagaRepositoryTest_Saga extends Saga
{
    /**
     * @param Event $event
     * @return void
     */
    public function on(Event $event)
    {
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return true;
    }
}

class SagaRepositoryTest_Event extends Event
{
}