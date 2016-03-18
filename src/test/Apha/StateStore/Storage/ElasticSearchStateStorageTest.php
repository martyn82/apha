<?php
declare(strict_types = 1);

namespace Apha\StateStore\Storage;

use Apha\Message\Event;
use Apha\Serializer\JsonSerializer;
use Apha\StateStore\Document;
use Elasticsearch\Client as ElasticClient;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Common\Exceptions\NoNodesAvailableException;
use JMS\Serializer\Annotation as Serializer;

class ElasticSearchStateStorageTest extends \PHPUnit_Framework_TestCase implements StateStorageTest
{
    /**
     * @var ElasticClient
     */
    private static $client;

    /**
     * @var string
     */
    private static $index;

    /**
     * @var string
     */
    private static $type;

    /**
     */
    public static function setUpBeforeClass()
    {
        if (!class_exists('Elasticsearch\\Client')) {
            self::markTestSkipped("ElasticSearch library not found.");
        }

        self::$index = uniqid('test_');
        self::$type = uniqid();

        self::$client = ClientBuilder::create()->build();
    }

    /**
     */
    public static function tearDownAfterClass()
    {
        self::$index = null;
        self::$type = null;
        self::$client = null;
    }

    /**
     */
    protected function setUp()
    {
        try {
            self::$client->indices()->create(['index' => self::$index]);
        } catch (NoNodesAvailableException $e) {
            self::markTestSkipped($e->getMessage());
        }
    }

    /**
     */
    protected function tearDown()
    {
        try {
            self::$client->indices()->delete(['index' => self::$index]);
        } catch (\Exception $e) {
        }
    }

    /**
     * @return ElasticClient
     */
    private function createClient(): ElasticClient
    {
        return self::$client;
    }

    /**
     * @return \Apha\Serializer\Serializer
     */
    private function createSerializer(): \Apha\Serializer\Serializer
    {
        return new JsonSerializer();
    }

    /**
     * @return Document
     */
    private function createDocument(): Document
    {
        return $this->getMockBuilder(Document::class)
            ->getMockForAbstractClass();
    }

    /**
     * @param ElasticClient $client
     * @param \Apha\Serializer\Serializer $serializer
     * @param string $documentClass [optional]
     * @return ElasticSearchStateStorage
     */
    private function createStorage(
        ElasticClient $client,
        \Apha\Serializer\Serializer $serializer,
        string $documentClass = null
    ) : ElasticSearchStateStorage
    {
        if ($documentClass == null) {
            $documentClass = Document::class;
        }

        return new ElasticSearchStateStorage($client, $serializer, $documentClass, self::$index, self::$type);
    }

    /**
     * @test
     */
    public function upsertAddsDocumentToStorage()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();
        $document = $this->createDocument();

        $storage = $this->createStorage($client, $serializer);
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

        $storage = $this->createStorage($client, $serializer);
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

        $storage = $this->createStorage($client, $serializer);
        $storage->delete('1');
    }

    /**
     * @test
     */
    public function deleteIsIdempotent()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $storage = $this->createStorage($client, $serializer);
        $storage->delete('1');
    }

    /**
     * @test
     */
    public function findRetrievesDocumentByIdentity()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $identity = '1';
        $document = new ElasticSearchStateStorageTest_Document('foo', 'bar');

        $storage = $this->createStorage($client, $serializer, ElasticSearchStateStorageTest_Document::class);
        $storage->upsert($identity, $document);

        $found = $storage->find($identity);

        self::assertEquals($document, $found);
    }

    /**
     * @test
     * @expectedException \Apha\StateStore\Storage\DocumentNotFoundException
     */
    public function findThrowsExceptionIfDocumentCannotBeFound()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $storage = $this->createStorage($client, $serializer, ElasticSearchStateStorageTest_Document::class);
        $storage->find('1');
    }

    /**
     * @test
     */
    public function clearRemovesAllDocumentsFromStorage()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $storage = $this->createStorage($client, $serializer);
        $storage->clear();
    }

    /**
     * @test
     */
    public function clearIsIdempotent()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $storage = $this->createStorage($client, $serializer);
        $storage->clear();
    }

    /**
     * @test
     */
    public function callClearWithNonExistingIndexIsOk()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $storage = $this->createStorage($client, $serializer);
        self::$client->indices()->delete(['index' => self::$index]);

        $storage->clear();
    }

    /**
     * @param int $count
     * @param ElasticClient $client
     * @param \Apha\Serializer\Serializer $serializer
     * @return Document[]
     */
    public function documentsProvider(int $count, ElasticClient $client, \Apha\Serializer\Serializer $serializer): array
    {
        $documents = [];
        $result = [];

        for ($i = 0; $i < $count; $i++) {
            $documents[$i] = new ElasticSearchStateStorageTest_Document('foo', 'bar');

            $result[] = [
                '_source' => $serializer->serialize($documents[$i])
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

        $storage = $this->createStorage($client, $serializer, ElasticSearchStateStorageTest_Document::class);
        $storage->findAll(0, 3);
    }

    /**
     * @test
     */
    public function findAllRetrievesEmptyArrayIfNoneFound()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $storage = $this->createStorage($client, $serializer, ElasticSearchStateStorageTest_Document::class);
        self::assertEmpty($storage->findAll());
    }

    /**
     * @test
     */
    public function findByRetrievesAPageOfDocumentsMatchingCriteria()
    {
        $client = $this->createClient();
        $serializer = $this->createSerializer();

        $identity = '1';
        $document = new ElasticSearchStateStorageTest_Document('foo', 'bar');

        $storage = $this->createStorage($client, $serializer, ElasticSearchStateStorageTest_Document::class);
        $storage->upsert($identity, $document);

        $matches = $storage->findBy(['foo' => 'foo'], 0, 3);

        self::assertCount(1, $matches);
        self::assertEquals($matches[0], $document);
    }
}

class ElasticSearchStateStorageTest_Document implements Document
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
    public function getFoo(): string
    {
        return $this->foo;
    }

    /**
     * @return string
     */
    public function getBar(): string
    {
        return $this->bar;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return '1';
    }

    /**
     * @return int
     */
    public function getVersion(): int
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