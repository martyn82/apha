<?php
declare(strict_types = 1);
require_once dirname(__FILE__) . '/../vendor/autoload.php';

/*
 * This example demonstrates the use of an EventStore to store events generated by executing commands on an aggregate.
 * In this example the aggregate is the User class.
 *
 * The command CreateUser accepts a UUID to identify the new User that will be created by executing the command.
 * The aggregate User will be created by handling this command. The aggregate generates a UserCreated event
 * upon its creation.
 *
 * The new User will be stored by means of a Repository.
 * The repository saves the uncommitted changes of the User into the EventStore.
 * If successfully saved, the EventStore publishes each event to the event bus where multiple handlers may react on the
 * events.
 *
 * In this case, upon execution, the UserCreatedHandler prints the contents of the EventStore to show how the events are
 * stored.
 */

/**
 * Command class to create a user.
 */
final class CreateUser implements \Apha\Message\Command
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;

    /**
     * @param \Ramsey\Uuid\UuidInterface $id
     */
    public function __construct(\Ramsey\Uuid\UuidInterface $id)
    {
        $this->id = $id;
    }

    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getId()
    {
        return $this->id;
    }
}

/**
 * Event class UserCreated.
 */
final class UserCreated extends \Apha\Message\Event
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;

    /**
     * @param \Ramsey\Uuid\UuidInterface $id
     */
    public function __construct(\Ramsey\Uuid\UuidInterface $id)
    {
        $this->id = $id;
    }

    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getId()
    {
        return $this->id;
    }
}

/**
 * Handler for CreateUser command.
 */
class CreateUserHandler implements \Apha\MessageHandler\CommandHandler
{
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
     * @param \Apha\Message\Command $command
     */
    public function handle(\Apha\Message\Command $command)
    {
        $commandName = get_class($command);
        echo "Handling command: '{$commandName}'.\n";

        $user = User::create($command->getId());
        $this->repository->store($user);
    }
}

/**
 * Handler for UserCreated event.
 */
class UserCreatedHandler implements \Apha\MessageHandler\EventHandler
{
    /**
     * @var \Apha\EventStore\Storage\EventStorage
     */
    private $storage;

    /**
     * @param \Apha\EventStore\Storage\EventStorage $storage
     */
    public function __construct(\Apha\EventStore\Storage\EventStorage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param \Apha\Message\Event $event
     */
    public function on(\Apha\Message\Event $event)
    {
        $eventName = $event->getEventName();
        echo "Handling event: '{$eventName}'.\n";

        $events = $this->storage->find($event->getId()->toString());
        var_dump($events);
    }
}

class Repository
{
    /**
     * @var \Apha\EventStore\EventStore
     */
    private $eventStore;

    /**
     * @param \Apha\EventStore\EventStore $eventStore
     */
    public function __construct(\Apha\EventStore\EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }

    /**
     * @param \Apha\Domain\AggregateRoot $aggregateRoot
     * @param int $expectedPlayHead
     * @throws \Apha\EventStore\ConcurrencyException
     */
    public function store(\Apha\Domain\AggregateRoot $aggregateRoot, int $expectedPlayHead = -1)
    {
        $this->eventStore->save(
            $aggregateRoot->getId(),
            $aggregateRoot->getUncommittedChanges(),
            $expectedPlayHead
        );
    }
}

/**
 * Aggregate class that represents a User.
 */
class User extends \Apha\Domain\AggregateRoot
{
    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $id;

    /**
     * @param \Ramsey\Uuid\UuidInterface $id
     * @return User
     */
    public static function create(\Ramsey\Uuid\UuidInterface $id) : self
    {
        $instance = new self($id);
        $instance->applyChange(new UserCreated($id));
        return $instance;
    }

    /**
     * @param \Ramsey\Uuid\UuidInterface $id
     */
    protected function __construct(\Ramsey\Uuid\UuidInterface $id)
    {
        $this->id = $id;
        parent::__construct();
    }

    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getId() : \Ramsey\Uuid\UuidInterface
    {
        return $this->id;
    }

    /**
     * @param UserCreated $event
     */
    protected function applyUserCreated(UserCreated $event)
    {
        // No changes to the internal state of this aggregate are required
    }
}

// The storage device to store event data
$eventStorage = new \Apha\EventStore\Storage\MemoryEventStorage();

// A new event bus with a mapping to specify what handlers to call for what event.
$eventBus = new \Apha\MessageBus\EventBus([
    UserCreated::class => [new UserCreatedHandler($eventStorage)]
]);

// An event store to store events
$eventStore = new \Apha\EventStore\EventStore(
    $eventBus,
    $eventStorage,
    \JMS\Serializer\SerializerBuilder::create()->build(),
    new \Apha\EventStore\EventClassMap([
        UserCreated::class
    ])
);

// A repository for objects of type Aggregate
$repository = new Repository($eventStore);

// A new command bus with a mapping to specify what handler to call for what command.
$commandBus = new \Apha\MessageBus\CommandBus([
    CreateUser::class => new CreateUserHandler($repository)
]);

// Send the command
$commandBus->send(new CreateUser(\Ramsey\Uuid\Uuid::uuid4()));