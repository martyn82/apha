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

