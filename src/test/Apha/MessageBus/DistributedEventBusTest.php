<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Event;
use Apha\MessageQueue\MessageQueue;
use JMS\Serializer\SerializerBuilder;

class DistributedEventBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function publishEventToQueue()
    {
        $queue = $this->getMockBuilder(MessageQueue::class)
            ->setMethods(['enqueue'])
            ->getMockForAbstractClass();

        $serializer = SerializerBuilder::create()->build();

        $event = $this->getMockBuilder(Event::class)
            ->getMockForAbstractClass();

        $queue->expects(self::once())
            ->method('enqueue');

        $eventBus = new DistributedEventBus($queue, $serializer);
        $eventBus->publish($event);
    }
}