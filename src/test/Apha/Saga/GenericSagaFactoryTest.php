<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\Message\Event;

/**
 * @group saga
 */
class GenericSagaFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createSagaCreatesASaga()
    {
        $sagaType = GenericSagaFactoryTest_Saga::class;
        $identity = Identity::createNew();

        $factory = new GenericSagaFactory();
        $saga = $factory->createSaga($sagaType, $identity, new AssociationValues([]));

        self::assertEquals($identity, $saga->getId());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function createSagaThrowsExceptionIfSagaUnsupported()
    {
        $factory = new GenericSagaFactory();
        $factory->createSaga(\stdClass::class, Identity::createNew(), new AssociationValues([]));
    }
}

class GenericSagaFactoryTest_Saga extends Saga
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
    }
}
