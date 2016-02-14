<?php
declare(strict_types = 1);

namespace Apha\ReadStore\Storage;

use Apha\ReadStore\Document;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use JMS\Serializer\SerializerInterface;

class ElasticSearchReadStorage implements ReadStorage
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $type;

    /**
     * @param Client $client
     * @param SerializerInterface $serializer
     * @param string $documentClass
     * @param string $index
     * @param string $type
     */
    public function __construct(
        Client $client,
        SerializerInterface $serializer,
        string $documentClass,
        string $index,
        string $type
    ) {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->documentClass = $documentClass;
        $this->index = $index;
        $this->type = $type;
    }

    /**
     * @param string $identity
     * @param bool $refresh
     * @param Document $document
     * @return array
     */
    private function createParams(string $identity = null, bool $refresh = false, Document $document = null) : array
    {
        $params = [
            'index' => $this->index,
            'type' => $this->type
        ];

        if ($identity != null) {
            $params['id'] = $identity;
        }

        if ($document != null) {
            $params['body'] = $this->serializer->serialize($document, 'json');
        }

        if ($refresh) {
            $params['refresh'] = true;
        }

        return $params;
    }

    /**
     * @param string $identity
     * @param Document $document
     * @return void
     */
    public function upsert(string $identity, Document $document)
    {
        $this->client->index($this->createParams($identity, true, $document));
    }

    /**
     * @param string $identity
     * @return void
     */
    public function delete(string $identity)
    {
        try {
            $this->client->delete($this->createParams($identity, true));
        } catch (Missing404Exception $e) {
            // don't care if document can't be found
        }
    }

    /**
     * @param string $identity
     * @return Document
     * @throws DocumentNotFoundException
     */
    public function find(string $identity) : Document
    {
        try {
            $data = $this->client->get($this->createParams($identity));
            return $this->serializer->deserialize($data, $this->documentClass, 'json');
        } catch (Missing404Exception $e) {
            throw new DocumentNotFoundException($identity);
        }
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return Document[]
     */
    public function findAll(int $offset = 0, int $limit = 500) : array
    {
        $query = [
            'match_all' => []
        ];

        return $this->query($query, $offset, $limit);
    }

    /**
     * @return void
     */
    public function clear()
    {
        try {
            $this->client->indices()->delete(['index' => $this->index]);
        } catch (Missing404Exception $e) {
            // don't care if index cannot be found
        }

        $this->createIndex();
    }

    /**
     */
    private function createIndex()
    {
        $this->client->indices()->create(['index' => $this->index]);
    }

    /**
     * @param array $criteria
     * @param int $offset
     * @param int $limit
     * @return Document[]
     */
    public function findBy(array $criteria, int $offset = 0, int $limit = 500) : array
    {
        $query = [
            'filtered' => [
                'query' => [
                    'match_all' => []
                ]
            ],
            'filter' => [
                'term' => $criteria
            ]
        ];

        return $this->query($query, $offset, $limit);
    }

    /**
     * @param array $query
     * @param int $offset
     * @param int $limit
     * @return array
     */
    private function query(array $query, int $offset, int $limit) : array
    {
        $params = $this->createParams();
        $params['body'] = ['query' => $query];
        $params['from'] = $offset;
        $params['size'] = $limit;

        $result = $this->client->search($params);

        if (empty($result) || !array_key_exists('hits', $result)) {
            return [];
        }

        return array_map(
            function (array $hit) {
                return $this->serializer->deserialize($hit['_source'], $this->documentClass, 'json');
            },
            $result['hits']['hits']
        );
    }
}