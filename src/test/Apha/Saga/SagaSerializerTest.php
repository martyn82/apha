<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Domain\Identity;
use Apha\Message\Event;
use Apha\Serializer\JsonSerializer;
use Apha\Serializer\Serializer;

class SagaSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Serializer
     */
    private function createSerializer(): Serializer
    {
        return $this->getMockBuilder(Serializer::class)
            ->getMock();
    }

    /**
     * @return SagaFactory
     */
    public function createFactory(): SagaFactory
    {
        return $this->getMockBuilder(SagaFactory::class)
            ->getMock();
    }

    /**
     * @test
     */
    public function serializeWillSerializeTheSaga()
    {
        $serializer = $this->createSerializer();
        $factory = $this->createFactory();

        $saga = $this->getMockBuilder(Saga::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serializer->expects(self::once())
            ->method('serialize')
            ->with($saga);

        $sagaSerializer = new SagaSerializer($serializer, $factory);
        $sagaSerializer->serialize($saga);
    }

    /**
     * @test
     */
    public function deserializeWillHydrateDeserializedSaga()
    {
        $serializer = new JsonSerializer();
        $factory = $this->createFactory();

        $saga = new SagaSerializerTest_Saga(Identity::createNew(), new AssociationValues([]));
        $sagaType = get_class($saga);
        $serializedSaga = $serializer->serialize($saga);

        $factory->expects(self::once())
            ->method('hydrate')
            ->with($saga);

        $sagaSerializer = new SagaSerializer($serializer, $factory);
        $sagaSerializer->deserialize($serializedSaga, $sagaType);
    }
}

class SagaSerializerTest_Saga extends Saga
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
