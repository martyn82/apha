<?php
declare(strict_types = 1);

namespace Apha\Testing;

use Apha\CommandHandling\CommandHandler;
use Apha\Domain\AggregateFactory;
use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;
use Apha\Message\Command;
use Apha\Message\Event;
use Apha\Message\Events;
use PHPUnit_Framework_TestCase as TestCase;

class Scenario
{
    /**
     * @var TestCase
     */
    private $testCase;

    /**
     * @var TraceableEventStore
     */
    private $eventStore;

    /**
     * @var CommandHandler
     */
    private $commandHandler;

    /**
     * @var AggregateFactory
     */
    private $factory;

    /**
     * @var AggregateRoot
     */
    private $aggregate;

    /**
     * @param AggregateFactory $aggregateFactory
     * @param TestCase $testCase
     * @param TraceableEventStore $eventStore
     * @param CommandHandler $commandHandler
     */
    public function __construct(
        AggregateFactory $aggregateFactory,
        TestCase $testCase,
        TraceableEventStore $eventStore,
        CommandHandler $commandHandler
    )
    {
        $this->factory = $aggregateFactory;
        $this->testCase = $testCase;
        $this->eventStore = $eventStore;
        $this->commandHandler = $commandHandler;
    }

    /**
     * @param array $events
     * @return AggregateRoot
     */
    private function getAggregate(array $events = []): AggregateRoot
    {
        if ($this->aggregate == null) {
            $this->aggregate = $this->factory->createAggregate(Identity::createNew(), new Events($events));
        }

        return $this->aggregate;
    }

    /**
     * @param array $events
     * @return Scenario
     */
    public function given(array $events = []): self
    {
        if (empty($events)) {
            return $this;
        }

        $aggregate = $this->getAggregate($events);
        $this->eventStore->save($aggregate->getId(), get_class($aggregate), new Events($events), -1);

        return $this;
    }

    /**
     * @param array $commands
     * @return Scenario
     */
    public function when(array $commands = []): self
    {
        $this->eventStore->getEventBus()->clearLog();

        if (empty($commands)) {
            return $this;
        }

        /* @var $command Command */
        foreach ($commands as $command) {
            $this->commandHandler->handle($command);
        }

        return $this;
    }

    /**
     * @param array $expectEvents
     */
    public function then(array $expectEvents = [])
    {
        $actualEvents = $this->eventStore->getEventBus()->getPublishedEvents();

        /* @var $event Event */
        foreach ($expectEvents as $index => $event) {
            if (array_key_exists($index, $actualEvents)) {
                $event->setVersion($actualEvents[$index]->getVersion());
            }
        }

        $this->testCase->assertEquals($expectEvents, $actualEvents);
    }
}
