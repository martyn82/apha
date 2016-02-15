<?php
declare(strict_types = 1);

namespace Apha\EventStore\Storage;

use Apha\EventStore\EventDescriptor;
use MongoDB\Collection;
use MongoDB\Driver\Cursor;
use MongoDB\InsertOneResult;

class MongoDbEventStorageTest extends \PHPUnit_Framework_TestCase implements EventStorageTest
{
    /**
     * @return Collection
     */
    private function getCollection()
    {
        return $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @test
     */
    public function containsReturnsFalseIfIdentityDoesNotExistInStorage()
    {
        $identity = 'foo';
        $identityField = 'identity';

        $collection = $this->getCollection();

        $collection->expects(self::once())
            ->method('count')
            ->with([$identityField => $identity])
            ->willReturn(0);

        $storage = new MongoDbEventStorage($collection, $identityField);
        self::assertFalse($storage->contains($identity));
    }

    /**
     * @test
     */
    public function containsReturnsTrueIfIdentityExistsInStorage()
    {
        $identity = 'foo';
        $identityField = 'identity';

        $collection = $this->getCollection();

        $collection->expects(self::once())
            ->method('count')
            ->with([$identityField => $identity])
            ->willReturn(1);

        $storage = new MongoDbEventStorage($collection, $identityField);
        self::assertTrue($storage->contains($identity));
    }

    /**
     * @test
     */
    public function appendAcceptsTheFirstEventForAnIdentity()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
        $identity = 'someidentity';
        $event = EventDescriptor::record(
            $identity,
            'someevent',
            '{}',
            1
        );

        $insertOneResult = $this->getMockBuilder(InsertOneResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $insertOneResult->expects(self::once())
            ->method('isAcknowledged')
            ->willReturn(true);

        $collection->expects(self::once())
            ->method('insertOne')
            ->with($event->toArray())
            ->willReturn($insertOneResult);

        $storage = new MongoDbEventStorage($collection, $identityField);
        self::assertTrue($storage->append($event));
    }

    /**
     * @test
     */
    public function appendAcceptsTheNextEventForAnIdentity()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
        $identity = 'someidentity';

        $event1 = EventDescriptor::record(
            $identity,
            'someevent',
            '{}',
            1
        );

        $event2 = EventDescriptor::record(
            $identity,
            'someevent',
            '{}',
            2
        );

        $insertOneResult = $this->getMockBuilder(InsertOneResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $insertOneResult->expects(self::any())
            ->method('isAcknowledged')
            ->willReturn(true);

        $collection->expects(self::exactly(2))
            ->method('insertOne')
            ->willReturn($insertOneResult);

        $storage = new MongoDbEventStorage($collection, $identityField);
        self::assertTrue($storage->append($event1));
        self::assertTrue($storage->append($event2));
    }

    /**
     * @test
     */
    public function findWillReturnEmptyResultIfIdentityDoesNotExist()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
        $identity  = 'identity';

        $collection->expects(self::once())
            ->method('find')
            ->with([$identityField => $identity])
            ->willReturn(new class {
                /**
                 * @return array
                 */
                public function toArray() : array
                {
                    return [];
                }
            });

        $storage = new MongoDbEventStorage($collection, $identityField);
        self::assertEmpty($storage->find($identity));
    }

    /**
     * @test
     */
    public function findWillReturnAllEventsInOrderForIdentity()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
        $identity  = 'identity';

        $event1 = EventDescriptor::record(
            $identity,
            'someevent1',
            '{}',
            1
        );
        $event2 = EventDescriptor::record(
            $identity,
            'someevent2',
            '{}',
            2
        );

        $insertOneResult = $this->getMockBuilder(InsertOneResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $insertOneResult->expects(self::any())
            ->method('isAcknowledged')
            ->willReturn(true);

        $collection->expects(self::any())
            ->method('insertOne')
            ->willReturn($insertOneResult);

        $collection->expects(self::once())
            ->method('find')
            ->with([$identityField => $identity])
            ->willReturn(new class ([$event1, $event2]) {
                /**
                 * @var array
                 */
                private $eventDescriptors;

                /**
                 * @param array $eventDescriptors
                 */
                public function __construct(array $eventDescriptors)
                {
                    $this->eventDescriptors = $eventDescriptors;
                }

                /**
                 * @return array
                 */
                public function toArray() : array
                {
                    return array_map(
                        function (EventDescriptor $event) : array {
                            return $event->toArray();
                        },
                        $this->eventDescriptors
                    );
                }
            });

        $storage = new MongoDbEventStorage($collection, $identityField);
        $storage->append($event1);
        $storage->append($event2);

        $events = $storage->find($identity);
        self::assertCount(2, $events);
        self::assertEquals($event1, $events[0]);
        self::assertEquals($event2, $events[1]);
    }

    /**
     * @test
     */
    public function findIdentitiesWillRetrieveAllKnownIdentities()
    {
        $collection = $this->getCollection();

        $identityField = 'identity';
        $identity1 = 'someidentity';
        $identity2 = 'someotheridentity';

        $event1 = EventDescriptor::record(
            $identity1,
            'someevent',
            '{}',
            1
        );
        $event2 = EventDescriptor::record(
            $identity2,
            'someevent',
            '{}',
            1
        );

        $insertOneResult = $this->getMockBuilder(InsertOneResult::class)
            ->disableOriginalConstructor()
            ->getMock();

        $insertOneResult->expects(self::any())
            ->method('isAcknowledged')
            ->willReturn(true);

        $collection->expects(self::any())
            ->method('insertOne')
            ->willReturn($insertOneResult);

        $collection->expects(self::once())
            ->method('distinct')
            ->with($identityField)
            ->willReturn(new class ([$identity1, $identity2]) {
                /**
                 * @var array
                 */
                private $identities;

                /**
                 * @param array $identities
                 */
                public function __construct(array $identities)
                {
                    $this->identities = $identities;
                }

                /**
                 * @return array
                 */
                public function toArray() : array
                {
                    return $this->identities;
                }
            });

        $storage = new MongoDbEventStorage($collection, $identityField);
        $storage->append($event1);
        $storage->append($event2);

        $identities = $storage->findIdentities();

        self::assertCount(2, $identities);
        self::assertContains($identity1, $identities);
        self::assertContains($identity2, $identities);
    }
}