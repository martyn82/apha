<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Command;
use Apha\MessageQueue\MessageQueue;
use JMS\Serializer\SerializerBuilder;

class DistributedCommandBusTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function sendCommandToQueue()
    {
        $queue = $this->getMockBuilder(MessageQueue::class)
            ->setMethods(['enqueue'])
            ->getMockForAbstractClass();

        $queue->expects(self::once())
            ->method('enqueue');

        $command = $this->getMockBuilder(Command::class)
            ->getMockForAbstractClass();

        $serializer = SerializerBuilder::create()->build();

        $commandBus = new DistributedCommandBus($queue, $serializer);
        $commandBus->send($command);
    }
}