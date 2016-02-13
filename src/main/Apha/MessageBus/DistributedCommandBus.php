<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Command;
use Apha\MessageQueue\MessageQueue;
use JMS\Serializer\SerializerInterface;

class DistributedCommandBus extends CommandBus
{
    /**
     * @var MessageQueue
     */
    private $queue;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param MessageQueue $queue
     * @param SerializerInterface $serializer
     */
    public function __construct(MessageQueue $queue, SerializerInterface $serializer)
    {
        $this->queue = $queue;
        $this->serializer = $serializer;
    }

    /**
     * @param Command $command
     */
    public function send(Command $command)
    {
        $this->queue->enqueue($this->serializer->serialize($command, 'json'));
    }
}