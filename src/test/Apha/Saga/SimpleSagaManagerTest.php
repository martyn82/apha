<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\Message\Event;

class SimpleSagaManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return SagaRepository
     */
    private function createRepository(): SagaRepository
    {
        return $this->getMockBuilder(SagaRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return SagaFactory
     */
    private function createFactory(): SagaFactory
    {
        return $this->getMockBuilder(SagaFactory::class)
            ->getMock();
    }

    /**
     * @test
     */
    public function onEventDelegatesEventToSagas()
    {
        $repository = $this->createRepository();
        $factory = $this->createFactory();

        $aggregateIdentity = Identity::createNew();
        $event = new SimpleSagaManagerTest_Event($aggregateIdentity);

        $associationValues = new AssociationValues([
            new AssociationValue('identity', $aggregateIdentity->getValue())
        ]);

        $sagaIdentity = Identity::createNew();
        $sagaInstance = $this->getMockBuilder(SimpleSagaManagerTest_Saga::class)
            ->setConstructorArgs([$sagaIdentity, $associationValues])
            ->getMock();

        $sagaTypes = [get_class($sagaInstance)];

        $repository->expects(self::once())
            ->method('find')
            ->with($sagaTypes[0], $associationValues->getIterator()->current())
            ->willReturn([$sagaIdentity]);

        $repository->expects(self::once())
            ->method('load')
            ->with($sagaIdentity)
            ->willReturn($sagaInstance);

        $repository->expects(self::once())
            ->method('commit')
            ->with($sagaInstance);

        $sagaInstance->expects(self::once())
            ->method('on')
            ->with($event);

        $manager = new SimpleSagaManager($repository, $factory, $sagaTypes);
        $manager->on($event);
    }
}

class SimpleSagaManagerTest_Event extends Event
{
    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->setIdentity($identity);
    }
}

class SimpleSagaManagerTest_Saga extends Saga
{
    /**
     * @param Event $event
     * @return void
     */
    public function on(Event $event)
    {
        // TODO: Implement on() method.
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        // TODO: Implement isActive() method.
    }
}
