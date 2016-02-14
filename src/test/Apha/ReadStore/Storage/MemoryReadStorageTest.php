<?php
declare(strict_types = 1);

namespace Apha\ReadStore\Storage;

use Apha\Domain\Identity;
use Apha\ReadStore\Document;

class MemoryReadStorageTest extends \PHPUnit_Framework_TestCase implements ReadStorageTest
{
    /**
     * @test
     */
    public function upsertAddsDocumentToStorage()
    {
        $document = $this->getMockBuilder(Document::class)
            ->getMockForAbstractClass();

        $identity = 'someidentity';

        $storage = new MemoryReadStorage();
        $storage->upsert($identity, $document);

        self::assertSame($document, $storage->find($identity));
    }

    /**
     * @test
     */
    public function upsertUpdatesExistingDocument()
    {
        $initialDocument = $this->getMockBuilder(Document::class)
            ->getMockForAbstractClass();

        $secondDocument = $this->getMockBuilder(Document::class)
            ->getMockForAbstractClass();

        $identity = 'someidentity';

        $storage = new MemoryReadStorage();
        $storage->upsert($identity, $initialDocument);
        $storage->upsert($identity, $secondDocument);

        self::assertSame($secondDocument, $storage->find($identity));
    }

    /**
     * @test
     * @expectedException \Apha\ReadStore\Storage\DocumentNotFoundException
     */
    public function deleteRemovesDocumentFromStorage()
    {
        $document = $this->getMockBuilder(Document::class)
            ->getMockForAbstractClass();

        $identity = 'someidentity';

        $storage = new MemoryReadStorage();
        $storage->upsert($identity, $document);

        $storage->delete($identity);
        $storage->find($identity);
    }

    /**
     * @test
     */
    public function deleteIsIdempotent()
    {
        $storage = new MemoryReadStorage();
        self::assertEmpty($storage->delete('nonexistent'));
    }

    /**
     * @test
     */
    public function findRetrievesDocumentByIdentity()
    {
        $identity = 'someidentity';

        $document = $this->getMockBuilder(Document::class)
            ->getMockForAbstractClass();

        $storage = new MemoryReadStorage();
        $storage->upsert($identity, $document);

        self::assertSame($document, $storage->find($identity));
    }

    /**
     * @test
     * @expectedException \Apha\ReadStore\Storage\DocumentNotFoundException
     */
    public function findThrowsExceptionIfDocumentCannotBeFound()
    {
        $storage = new MemoryReadStorage();
        $storage->find('someidentity');
    }

    /**
     * @test
     * @expectedException \Apha\ReadStore\Storage\DocumentNotFoundException
     */
    public function clearRemovesAllDocumentsFromStorage()
    {
        $identity = 'someidentity';

        $document = $this->getMockBuilder(Document::class)
            ->getMockForAbstractClass();

        $storage = new MemoryReadStorage();
        $storage->upsert($identity, $document);

        $storage->clear();
        $storage->find($identity);
    }

    /**
     * @test
     * @expectedException \Apha\ReadStore\Storage\DocumentNotFoundException
     */
    public function clearIsIdempotent()
    {
        $storage = new MemoryReadStorage();
        $storage->clear();

        $storage->find('someidentity');
    }

    /**
     * @param int $count
     * @param MemoryReadStorage $storage
     * @return Document[]
     */
    public function documentsProvider(int $count, MemoryReadStorage $storage) : array
    {
        $documents = [];

        for ($i = 0; $i < $count; $i++) {
            $documents[$i] = $this->getMockBuilder(Document::class)
                ->setMethods(['getFoo', 'getBar'])
                ->getMockForAbstractClass();

            $storage->upsert(Identity::createNew()->getValue(), $documents[$i]);
        }

        return $documents;
    }

    /**
     * @test
     */
    public function findAllRetrievesAPageOfDocuments()
    {
        $storage = new MemoryReadStorage();
        $documents = $this->documentsProvider(6, $storage);

        // retrieve first page
        $page1 = $storage->findAll(0, 3);

        self::assertCount(3, $page1);
        self::assertContains($documents[0], $page1);
        self::assertContains($documents[1], $page1);
        self::assertContains($documents[2], $page1);

        // retrieve second page
        $page2 = $storage->findAll(3, 3);

        self::assertCount(3, $page2);
        self::assertContains($documents[3], $page2);
        self::assertContains($documents[4], $page2);
        self::assertContains($documents[5], $page2);
    }

    /**
     * @test
     */
    public function findAllRetrievesEmptyArrayIfNoneFound()
    {
        $storage = new MemoryReadStorage();
        self::assertEmpty($storage->findAll());
    }

    /**
     * @test
     */
    public function findByRetrievesAPageOfDocumentsMatchingCriteria()
    {
        $storage = new MemoryReadStorage();
        $documents = $this->documentsProvider(6, $storage);

        $documents[0]->expects(self::any())
            ->method('getFoo')
            ->willReturn('foo');

        $documents[0]->expects(self::any())
            ->method('getBar')
            ->willReturn('bar');

        $documents[3]->expects(self::any())
            ->method('getBar')
            ->willReturn('bar');

        $documents[4]->expects(self::any())
            ->method('getBar')
            ->willReturn('bar');

        $page = $storage->findBy(['foo' => 'foo', 'bar' => 'bar']);

        self::assertCount(1, $page);
        self::assertSame($documents[0], $page[0]);
    }
}