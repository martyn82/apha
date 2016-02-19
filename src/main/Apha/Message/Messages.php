<?php
declare(strict_types = 1);

namespace Apha\Message;

class Messages implements \IteratorAggregate
{
    /**
     * @var Message[]
     */
    private $messages;

    /**
     * @param Message[] $messages
     */
    public function __construct(array $messages = [])
    {
        $this->messages = array_map(
            function (Message $message) : Message {
                return $message;
            },
            $messages
        );
    }

    /**
     * @param Message $message
     */
    public function add(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * @return int
     */
    public function size() : int
    {
        return count($this->messages);
    }

    /**
     */
    public function clear()
    {
        $this->messages = [];
    }

    /**
     * @return \Iterator
     */
    public function getIterator() : \Iterator
    {
        return new \ArrayIterator($this->messages);
    }
}