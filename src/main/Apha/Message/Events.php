<?php
declare(strict_types = 1);

namespace Apha\Message;

class Events implements \IteratorAggregate
{
    /**
     * @var Event[]
     */
    private $events;

    /**
     * @param Event[] $events
     */
    public function __construct(array $events = [])
    {
        $this->events = array_map(
            function (Event $event) : Event {
                return $event;
            },
            $events
        );
    }

    /**
     * @param Event $event
     */
    public function add(Event $event)
    {
        $this->events[] = $event;
    }

    /**
     * @return int
     */
    public function size() : int
    {
        return count($this->events);
    }

    /**
     */
    public function clear()
    {
        $this->events = [];
    }

    /**
     * @return \Iterator
     */
    public function getIterator() : \Iterator
    {
        return new \ArrayIterator($this->events);
    }
}