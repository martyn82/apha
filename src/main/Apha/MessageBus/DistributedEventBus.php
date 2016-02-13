<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Event;
use Apha\MessageQueue\MessageQueue;
use JMS\Serializer\SerializerInterface;

class DistributedEventBus extends EventBus
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
     * @param Event $event
     */
    public function publish(Event $event)
    {
        $this->queue->enqueue($this->serializer->serialize($event, 'json'));
    }
}