<?php
declare(strict_types=1);

namespace Domain;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Message\{Event, Events};
use Ramsey\Uuid\{Uuid, UuidInterface};

class AggregateRootTest extends \PHPUnit_Framework_TestCase
{
    public function testMarkChangesCommittedClearsEventLog()
    {
        $aggregateRoot = new AggregateRootTest_AggregateRoot();

        $event = new AggregateRootTest_Event();

        $aggregateRoot->applyChange($event);
        self::assertCount(1, $aggregateRoot->getUncommittedChanges()->getIterator());

        $aggregateRoot->markChangesCommitted();
        self::assertCount(0, $aggregateRoot->getUncommittedChanges()->getIterator());
    }

    public function testLoadFromHistoryAppliesEventsFromHistory()
    {
        $aggregateRoot = new AggregateRootTest_AggregateRoot();

        $events = new Events([
            new AggregateRootTest_Event()
        ]);

        $aggregateRoot->loadFromHistory($events);

        self::assertEquals(count($events), $aggregateRoot->getAppliedEventsCount());
        self::assertCount(0, $aggregateRoot->getUncommittedChanges()->getIterator());
    }

    /**
     * @expectedException \Apha\Domain\UnsupportedEventException
     */
    public function testApplyUnsupportedEventThrowsException()
    {
        $aggregateRoot = new AggregateRootTest_AggregateRoot();
        $event = new AggregateRootTest_UnsupportedEvent();

        $aggregateRoot->applyChange($event);
    }
}

class AggregateRootTest_UnsupportedEvent extends Event {}
class AggregateRootTest_Event extends Event {}

class AggregateRootTest_AggregateRoot extends AggregateRoot
{
    private $appliedEventsCount = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function getId() : UuidInterface
    {
        return Uuid::uuid4();
    }

    public function applyChange(Event $event)
    {
        parent::applyChange($event);
    }

    protected function applyAggregateRootTest_Event(Event $event)
    {
        $this->appliedEventsCount++;
    }

    public function getAppliedEventsCount()
    {
        return $this->appliedEventsCount;
    }
}