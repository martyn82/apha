<?php
declare(strict_types = 1);

namespace Apha\EventStore\Storage;

use Apha\EventStore\EventDescriptor;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

class MongoDbEventStorage implements EventStorage
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var string
     */
    private $identityField;

    /**
     * @param Collection $collection
     * @param string $identityField
     */
    public function __construct(Collection $collection, string $identityField)
    {
        $this->collection = $collection;
        $this->identityField = $identityField;

        $this->collection->createIndex([$this->identityField => 1]);
    }

    /**
     * @param string $identity
     * @return bool
     */
    public function contains(string $identity) : bool
    {
        return $this->collection->count([$this->identityField => $identity]) > 0;
    }

    /**
     * @param EventDescriptor $event
     * @return bool
     */
    public function append(EventDescriptor $event) : bool
    {
        $eventData = $event->toArray();
        $result = $this->collection->insertOne($eventData);
        return $result->isAcknowledged();
    }

    /**
     * @param string $identity
     * @return EventDescriptor[]
     */
    public function find(string $identity) : array
    {
        $cursor = $this->collection->find([$this->identityField => $identity]);

        return array_map(
            function (BSONDocument $eventData) : EventDescriptor {
                return EventDescriptor::reconstructFromArray($eventData->getArrayCopy());
            },
            $cursor->toArray()
        );
    }

    /**
     * @return string[]
     */
    public function findIdentities() : array
    {
        return $this->collection->distinct($this->identityField);
    }
}