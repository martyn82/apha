<?php
declare(strict_types = 1);

namespace Apha\StateStore\Storage;

use Apha\Serializer\ArrayConverter;
use Apha\Serializer\JsonSerializer;
use Apha\Serializer\Serializer;
use Apha\StateStore\Document;
use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

class MongoDbStateStorage implements StateStorage
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var ArrayConverter
     */
    private $converter;

    /**
     * @var string
     */
    private $identityField;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @param Collection $collection
     * @param string $identityField
     * @param string $documentClass
     */
    public function __construct(Collection $collection, string $identityField, string $documentClass)
    {
        $this->collection = $collection;
        $this->identityField = $identityField;
        $this->documentClass = $documentClass;

        $this->converter = new ArrayConverter();

        $this->collection->createIndex([$this->identityField => 1], ['unique']);
    }

    /**
     * @param string $identity
     * @param Document $document
     * @return void
     */
    public function upsert(string $identity, Document $document)
    {
        $converted = $this->converter->objectToArray($document);

        if ($this->collection->count([$this->identityField => $identity]) > 0) {
            $this->collection->replaceOne([$this->identityField => $identity], $converted);
        } else {
            $this->collection->insertOne($converted);
        }
    }

    /**
     * @param string $identity
     * @return void
     */
    public function delete(string $identity)
    {
        if ($this->collection->count([$this->identityField => $identity]) == 0) {
            return;
        }

        $this->collection->deleteOne([$this->identityField => $identity]);
    }

    /**
     * @param string $identity
     * @return Document
     * @throws DocumentNotFoundException
     */
    public function find(string $identity) : Document
    {
        /* @var $result BSONDocument */
        $result = $this->collection->findOne([$this->identityField => $identity]);

        if ($result == null) {
            throw new DocumentNotFoundException($identity);
        }

        $data = $result->getArrayCopy();
        unset($data['_id']);

        return $this->converter->arrayToObject(
            $data,
            $this->documentClass
        );
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return Document[]
     */
    public function findAll(int $offset = 0, int $limit = 500) : array
    {
        $cursor = $this->collection->find([], ['skip' => $offset, 'limit' => $limit]);

        return array_map(
            function (BSONDocument $document) : Document {
                $data = $document->getArrayCopy();
                unset($data['_id']);

                return $this->converter->arrayToObject($data, $this->documentClass);
            },
            $cursor->toArray()
        );
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->collection->drop();
    }

    /**
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     * @return Document[]
     */
    public function findBy(array $criteria, int $offset = 0, int $limit = 500) : array
    {
        $cursor = $this->collection->find($criteria, ['skip' => $offset, 'limit' => $limit]);

        return array_map(
            function (BSONDocument $document) : Document {
                $data = $document->getArrayCopy();
                unset($data['_id']);

                return $this->converter->arrayToObject($data, $this->documentClass);
            },
            $cursor->toArray()
        );
    }
}