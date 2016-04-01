<?php
declare(strict_types = 1);

namespace Apha\Testing;

use Apha\CommandHandling\CommandHandler;
use Apha\CommandHandling\TypedCommandHandler;
use Apha\Domain\AggregateFactory;
use Apha\Domain\AggregateRoot;
use Apha\Domain\GenericAggregateFactory;
use Apha\Domain\Identity;
use Apha\EventHandling\SimpleEventBus;
use Apha\EventStore\EventClassMap;
use Apha\EventStore\EventStore;
use Apha\EventStore\Storage\MemoryEventStorage;
use Apha\Message\Command;
use Apha\Message\Event;
use Apha\Repository\EventSourcingRepository;
use Apha\Repository\Repository;
use Apha\Serializer\JsonSerializer;
use Apha\StateStore\TypedEventApplicator;

/**
 * @group testing
 */
class ScenarioTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return AggregateFactory
     */
    private function createFactory(): AggregateFactory
    {
        return new GenericAggregateFactory(ScenarioTest_Aggregate::class);
    }

    /**
     * @param EventClassMap $eventClassMap
     * @return TraceableEventStore
     */
    private function createEventStore(EventClassMap $eventClassMap): TraceableEventStore
    {
        return new TraceableEventStore(
            new SimpleEventBus(),
            new MemoryEventStorage(),
            new JsonSerializer(),
            $eventClassMap
        );
    }

    /**
     * @param AggregateFactory $factory
     * @param EventStore $eventStore
     * @return Repository
     */
    private function createRepository(AggregateFactory $factory, EventStore $eventStore): Repository
    {
        return new EventSourcingRepository($factory, $eventStore);
    }

    /**
     * @test
     */
    public function givenNothingWhenNothingThenNothing()
    {
        $factory = $this->createFactory();
        $eventStore = $this->createEventStore(new EventClassMap([]));
        $repository = $this->createRepository($factory, $eventStore);
        $commandHandler = new ScenarioTest_CommandHandler($repository);

        $scenario = new Scenario($factory, $this, $eventStore, $commandHandler);
        $scenario->given()
            ->when()
            ->then();
    }

    /**
     * @test
     */
    public function aStoryOfOneEvent()
    {
        $factory = $this->createFactory();
        $eventStore = $this->createEventStore(new EventClassMap([
            ScenarioTest_Created::class
        ]));
        $repository = $this->createRepository($factory, $eventStore);
        $commandHandler = new ScenarioTest_CommandHandler($repository);

        $identity = Identity::createNew();

        $scenario = new Scenario($factory, $this, $eventStore, $commandHandler);
        $scenario->given()
            ->when([new ScenarioTest_Create($identity)])
            ->then([new ScenarioTest_Created($identity)]);
    }

    /**
     * @test
     */
    public function aStoryOfSubsequentEvents()
    {
        $factory = $this->createFactory();
        $eventStore = $this->createEventStore(new EventClassMap([
            ScenarioTest_Created::class,
            ScenarioTest_DoneThis::class,
            ScenarioTest_DoneThat::class
        ]));
        $repository = $this->createRepository($factory, $eventStore);
        $commandHandler = new ScenarioTest_CommandHandler($repository);

        $identity = Identity::createNew();

        $scenario = new Scenario($factory, $this, $eventStore, $commandHandler);
        $scenario->given([new ScenarioTest_Created($identity)])
            ->when([new ScenarioTest_DoThis($identity), new ScenarioTest_DoThat($identity)])
            ->then([new ScenarioTest_DoneThis($identity), new ScenarioTest_DoneThat($identity)]);
    }
}

class ScenarioTest_Aggregate extends AggregateRoot
{
    use TypedEventApplicator;

    /**
     * @var Identity
     */
    private $identity;

    /**
     * @param ScenarioTest_Create $command
     * @return ScenarioTest_Aggregate
     */
    public static function create(ScenarioTest_Create $command): self
    {
        $instance = new self();
        $instance->applyChange(new ScenarioTest_Created($command->getId()));
        return $instance;
    }

    /**
     * @return Identity
     */
    public function getId(): Identity
    {
        return $this->identity;
    }

    /**
     * @param ScenarioTest_DoThis $command
     */
    public function doThis(ScenarioTest_DoThis $command)
    {
        $this->applyChange(new ScenarioTest_DoneThis($command->getId()));
    }

    /**
     * @param ScenarioTest_DoThat $command
     */
    public function doThat(ScenarioTest_DoThat $command)
    {
        $this->applyChange(new ScenarioTest_DoneThat($command->getId()));
    }

    /**
     * @param ScenarioTest_Created $event
     */
    protected function applyScenarioTest_Created(ScenarioTest_Created $event)
    {
        $this->identity = $event->getIdentity();
    }

    /**
     * @param ScenarioTest_DoneThis $event
     */
    protected function applyScenarioTest_DoneThis(ScenarioTest_DoneThis $event)
    {
    }

    /**
     * @param ScenarioTest_DoneThat $event
     */
    protected function applyScenarioTest_DoneThat(ScenarioTest_DoneThat $event)
    {
    }
}

class ScenarioTest_CommandHandler implements CommandHandler
{
    use TypedCommandHandler;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param ScenarioTest_Create $command
     */
    public function handleScenarioTest_Create(ScenarioTest_Create $command)
    {
        $aggregate = ScenarioTest_Aggregate::create($command);
        $this->repository->store($aggregate);
    }

    /**
     * @param ScenarioTest_DoThis $command
     */
    public function handleScenarioTest_DoThis(ScenarioTest_DoThis $command)
    {
        $aggregate = $this->repository->findById($command->getId());
        $aggregate->doThis($command);
        $this->repository->store($aggregate, $aggregate->getVersion());
    }

    /**
     * @param ScenarioTest_DoThat $command
     */
    public function handleScenarioTest_DoThat(ScenarioTest_DoThat $command)
    {
        $aggregate = $this->repository->findById($command->getId());
        $aggregate->doThat($command);
        $this->repository->store($aggregate, $aggregate->getVersion());
    }
}

final class ScenarioTest_Create extends Command
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return Identity
     */
    public function getId(): Identity
    {
        return $this->identity;
    }
}

final class ScenarioTest_Created extends Event
{
    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->setIdentity($identity);
    }
}

final class ScenarioTest_DoThis extends Command
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return Identity
     */
    public function getId(): Identity
    {
        return $this->identity;
    }
}

final class ScenarioTest_DoneThis extends Event
{
    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->setIdentity($identity);
    }
}

final class ScenarioTest_DoThat extends Command
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return Identity
     */
    public function getId(): Identity
    {
        return $this->identity;
    }
}

final class ScenarioTest_DoneThat extends Event
{
    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->setIdentity($identity);
    }
}
