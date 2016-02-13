<?php
declare(strict_types = 1);

/**
 * A mock queueing system that delays the processing of messages to simulate a distributed application architecture.
 */
class QueueingSystem
{
    /**
     * @var array
     */
    private $queue = [];

    /**
     * @param string $message
     */
    public function enqueue(string $message)
    {
        $this->queue[] = $message;
    }

    /**
     * @return string
     */
    public function dequeue()
    {
        return array_pop($this->queue);
    }
}

/**
 * A distributed command bus that employs a queueing system for sending commands to distributed systems.
 */
class DistributedCommandBus extends \Apha\MessageBus\CommandBus
{
    /**
     * @var QueueingSystem
     */
    private $queue;

    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @param QueueingSystem $queue
     * @param \JMS\Serializer\SerializerInterface $serializer
     */
    public function __construct(QueueingSystem $queue, \JMS\Serializer\SerializerInterface $serializer)
    {
        $this->queue = $queue;
        $this->serializer = $serializer;
    }

    /**
     * @param \Apha\Message\Command $command
     */
    public function send(\Apha\Message\Command $command)
    {
        $this->queue->enqueue($this->serializer->serialize($command, 'json'));
    }
}