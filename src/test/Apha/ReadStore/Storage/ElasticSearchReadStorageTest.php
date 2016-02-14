<?php
declare(strict_types = 1);

namespace Apha\ReadStore\Storage;

use Apha\Message\Event;
use Apha\ReadStore\Document;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Namespaces\IndicesNamespace;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\Annotation as Serializer;

class ElasticSearchReadStorageTest extends \PHPUnit_Framework_TestCase implements ReadStorageTest
{
    /**
     * @return Client
     */
    private function createClient() : Client
    {
        return $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return SerializerInterface
     */
    private function createSerializer() : SerializerInterface
    {
        return SerializerBuilder::create()->build();
    }

    /**
     * @return Document
     */
    private function createDocument() : Document
    {
        return $this->getMockBuilder(Document::class)
            ->getMockForAbstractClass();
    }

    /**
     * @test
     */
    public function upsertAddsDocumentToStorage()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();
        $document = $this->createDocument();

        $client->expects(self::once())
            ->method('index');

        $storage = new ElasticSearchReadStorage($client, $serializer, Document::class, 'foo', 'bar');
        $storage->upsert('1', $document);
    }

    /**
     * @test
     */
    public function upsertUpdatesExistingDocument()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();
        $initialDocument = $this->createDocument();
        $secondDocument = $this->createDocument();

        $client->expects(self::exactly(2))
            ->method('index');

        $storage = new ElasticSearchReadStorage($client, $serializer, Document::class, 'foo', 'bar');
        $storage->upsert('1', $initialDocument);
        $storage->upsert('2', $secondDocument);
    }

    /**
     * @test
     */
    public function deleteRemovesDocumentFromStorage()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $client->expects(self::once())
            ->method('delete');

        $storage = new ElasticSearchReadStorage($client, $serializer, Document::class, 'foo', 'bar');
        $storage->delete('1');
    }

    /**
     * @test
     */
    public function deleteIsIdempotent()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $client->expects(self::once())
            ->method('delete')
            ->willThrowException(new Missing404Exception());

        $storage = new ElasticSearchReadStorage($client, $serializer, Document::class, 'foo', 'bar');
        $storage->delete('1');
    }

    /**
     * @test
     */
    public function findRetrievesDocumentByIdentity()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();
        $document = new ElasticSearchReadStorageTest_Document('foo', 'bar');

        $client->expects(self::once())
            ->method('get')
            ->willReturn($serializer->serialize($document, 'json'));

        $storage = new ElasticSearchReadStorage(
            $client,
            $serializer,
            ElasticSearchReadStorageTest_Document::class,
            'foo',
            'bar'
        );
        $found = $storage->find('1');

        self::assertEquals($document, $found);
    }

    /**
     * @test
     * @expectedException \Apha\ReadStore\Storage\DocumentNotFoundException
     */
    public function findThrowsExceptionIfDocumentCannotBeFound()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $client->expects(self::once())
            ->method('get')
            ->willThrowException(new Missing404Exception());

        $storage = new ElasticSearchReadStorage(
            $client,
            $serializer,
            ElasticSearchReadStorageTest_Document::class,
            'foo',
            'bar'
        );

        $storage->find('1');
    }

    /**
     * @test
     */
    public function clearRemovesAllDocumentsFromStorage()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $indices = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects(self::exactly(2))
            ->method('indices')
            ->willReturn($indices);

        $indices->expects(self::once())
            ->method('delete')
            ->with(['index' => 'foo']);

        $indices->expects(self::once())
            ->method('create')
            ->with(['index' => 'foo']);

        $storage = new ElasticSearchReadStorage(
            $client,
            $serializer,
            Document::class,
            'foo',
            'bar'
        );
        $storage->clear();
    }

    /**
     * @test
     */
    public function clearIsIdempotent()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $indices = $this->getMockBuilder(IndicesNamespace::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects(self::exactly(2))
            ->method('indices')
            ->willReturn($indices);

        $indices->expects(self::once())
            ->method('delete')
            ->with(['index' => 'foo'])
            ->willThrowException(new Missing404Exception());

        $storage = new ElasticSearchReadStorage(
            $client,
            $serializer,
            Document::class,
            'foo',
            'bar'
        );
        $storage->clear();
    }

    /**
     * @param int $count
     * @param Client $client
     * @param SerializerInterface $serializer
     * @return Document[]
     */
    public function documentsProvider(int $count, Client $client, SerializerInterface $serializer) : array
    {
        $documents = [];
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $documents[$i] = new ElasticSearchReadStorageTest_Document('foo', 'bar');

            $result[] = [
                '_source' => $serializer->serialize($documents[$i], 'json')
            ];
        }

        return $result;
    }

    /**
     * @test
     */
    public function findAllRetrievesAPageOfDocuments()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $storage = new ElasticSearchReadStorage(
            $client,
            $serializer,
            ElasticSearchReadStorageTest_Document::class,
            'foo',
            'bar'
        );

        $hits = $this->documentsProvider(6, $client, $serializer);
        $offset = 0;
        $limit = 3;

        $client->expects(self::once())
            ->method('search')
            ->with([
                'index' => 'foo',
                'type' => 'bar',
                'body' => [
                    'query' => [
                        'match_all' => []
                    ]
                ],
                'from' => $offset,
                'size' => $limit
            ])
            ->willReturn([
                'hits' => [
                    'hits' => $hits
                ]
            ]);

        $storage->findAll($offset, $limit);
    }

    /**
     * @test
     */
    public function findAllRetrievesEmptyArrayIfNoneFound()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $storage = new ElasticSearchReadStorage(
            $client,
            $serializer,
            ElasticSearchReadStorageTest_Document::class,
            'foo',
            'bar'
        );
        self::assertEmpty($storage->findAll());
    }

    /**
     * @test
     */
    public function findByRetrievesAPageOfDocumentsMatchingCriteria()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $hits = $this->documentsProvider(6, $client, $serializer);
        $offset = 0;
        $limit = 3;
        $criteria = ['foo' => 'foo'];

        $storage = new ElasticSearchReadStorage(
            $client,
            $serializer,
            ElasticSearchReadStorageTest_Document::class,
            'foo',
            'bar'
        );

        $client->expects(self::once())
            ->method('search')
            ->with([
                'index' => 'foo',
                'type' => 'bar',
                'body' => [
                    'query' => [
                        'filtered' => [
                            'query' => [
                                'match_all' => []
                            ]
                        ],
                        'filter' => [
                            'term' => $criteria
                        ]
                    ]
                ],
                'from' => $offset,
                'size' => $limit
            ])
            ->willReturn([
                'hits' => [
                    'hits' => $hits
                ]
            ]);

        $storage->findBy($criteria, $offset, $limit);
    }
}

class ElasticSearchReadStorageTest_Document implements Document
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $foo;

    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $bar;

    /**
     * @param string $foo
     * @param string $bar
     */
    public function __construct(string $foo, string $bar)
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    /**
     * @return string
     */
    public function getFoo() : string
    {
        return $this->foo;
    }

    /**
     * @return string
     */
    public function getBar() : string
    {
        return $this->bar;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return '1';
    }

    /**
     * @return int
     */
    public function getVersion() : int
    {
        return 1;
    }

    /**
     * @param Event $event
     * @return void
     */
    public function apply(Event $event)
    {
    }
}