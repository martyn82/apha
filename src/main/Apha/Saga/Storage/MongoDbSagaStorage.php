<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

use MongoDB\Collection;
use MongoDB\Model\BSONDocument;

class MongoDbSagaStorage implements SagaStorage
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;

        $this->collection->createIndex(['identity' => 1], ['unique']);
        $this->collection->createIndex(['type' => 1]);
        $this->collection->createIndex(['type'  => 1, 'associations' => 1]);
        $this->collection->createIndex(['identity' => 1, 'type' => 1, 'associations' => 1]);
    }

    /**
     * @param string $sagaType
     * @param string $identity
     * @param array $associationValues
     * @param string $data
     * @return void
     */
    public function insert(string $sagaType, string $identity, array $associationValues, string $data)
    {
        $sagaData = [
            'type' => $sagaType,
            'identity' => $identity,
            'associations' => $associationValues,
            'serialized' => $data
        ];

        $this->collection->insertOne($sagaData);
    }

    /**
     * @param string $sagaType
     * @param string $identity
     * @param array $associationValues
     * @param string $data
     * @return void
     */
    public function update(string $sagaType, string $identity, array $associationValues, string $data)
    {
        if ($this->collection->count(['identity' => $identity]) == 0) {
            $this->insert($sagaType, $identity, $associationValues, $data);
        } else {
            $this->collection->findOneAndUpdate(
                ['identity' => $identity, 'type' => $sagaType, 'associations' => $associationValues],
                [
                    '$set' => [
                        'serialized' => $data
                    ]
                ]
            );
        }
    }

    /**
     * @param string $identity
     * @return void
     */
    public function delete(string $identity)
    {
        $this->collection->findOneAndDelete(['identity' => $identity]);
    }

    /**
     * @param string $identity
     * @return string
     */
    public function findById(string $identity): string
    {
        $sagaData = $this->collection->findOne(['identity' => $identity]);

        if (empty($sagaData)) {
            return '';
        }

        return $sagaData['serialized'];
    }

    /**
     * @param string $sagaType
     * @param array $associationValue
     * @return string[]
     */
    public function find(string $sagaType, array $associationValue): array
    {
        $sagas = $this->collection->find(['type' => $sagaType, 'associations' => $associationValue]);

        return array_map(
            function (BSONDocument $sagaData): string {
                $sagaDataArray = $sagaData->getArrayCopy();
                return $sagaDataArray['identity'];
            },
            $sagas->toArray()
        );
    }
}
