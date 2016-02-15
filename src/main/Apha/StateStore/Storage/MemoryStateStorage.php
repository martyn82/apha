<?php
declare(strict_types = 1);

namespace Apha\StateStore\Storage;

use Apha\StateStore\Document;

class MemoryStateStorage implements StateStorage
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $identity
     * @param Document $document
     * @return void
     */
    public function upsert(string $identity, Document $document)
    {
        $this->data[$identity] = $document;
    }

    /**
     * @param string $identity
     * @return void
     */
    public function delete(string $identity)
    {
        if (!array_key_exists($identity, $this->data)) {
            return;
        }

        unset($this->data[$identity]);
    }

    /**
     * @param string $identity
     * @return Document
     * @throws DocumentNotFoundException
     */
    public function find(string $identity) : Document
    {
        if (!array_key_exists($identity, $this->data)) {
            throw new DocumentNotFoundException($identity);
        }

        return $this->data[$identity];
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return Document[]
     */
    public function findAll(int $offset = 0, int $limit = 500) : array
    {
        return array_values(
            array_slice($this->data, $offset, $limit)
        );
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->data = [];
    }

    /**
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     * @return Document[]
     */
    public function findBy(array $criteria, int $offset = 0, int $limit = 500) : array
    {
        // perform AND match on criteria
        $filteredDocuments = array_reduce(
            $this->data,
            function (array $result, Document $item) use ($criteria) : array {
                foreach ($criteria as $key => $value) {
                    $getterMethod = 'get' . ucfirst($key);

                    if ($item->{$getterMethod}() != $value) {
                        return $result;
                    }
                }

                $result[] = $item;
                return $result;
            },
            []
        );

        return array_slice($filteredDocuments, $offset, $limit);
    }
}