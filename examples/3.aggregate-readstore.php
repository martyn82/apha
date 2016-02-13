<?php
declare(strict_types = 1);
require_once __DIR__ . '/../vendor/autoload.php';

/*
 * This example demonstrates the use of a ReadStore to store documents. Documents are the snapshots of aggregates after
 * their history of events have been applied.
 *
 * Whenever an event on an aggregate is published, the readstore is updated by a handler.
 *
 * In this case, upon execution, the UserCreatedHandler, after updating the read store, prints the contents of the
 * document to show how the docuemnts are stored.
 */

/**
 * Command class to create a user.
 */
final class CreateUser implements \Apha\Message\Command
{
    /**
     * @var \Apha\Domain\Identity
     */
    private $id;

    /**
     * @param \Apha\Domain\Identity $id
     */
    public function __construct(\Apha\Domain\Identity $id)
    {
        $this->id = $id;
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getId() : \Apha\Domain\Identity
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
     * @var \Apha\Domain\Identity
     */
    private $id;

    /**
     * @param \Apha\Domain\Identity $id
     */
    public function __construct(\Apha\Domain\Identity $id)
    {
        $this->id = $id;
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getId() : \Apha\Domain\Identity
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
     * @var \Apha\ReadStore\Storage\MemoryReadStorage
     */
    private $storage;

    /**
     * @param \Apha\ReadStore\Storage\MemoryReadStorage $storage
     */
    public function __construct(\Apha\ReadStore\Storage\MemoryReadStorage $storage)
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

        try {
            $document = $this->storage->find($event->getId()->getValue());
            $document->apply($event);
        } catch (\Apha\ReadStore\Storage\DocumentNotFoundException $e) {
            $document = new UserDocument($event->getId()->getValue(), $event->getVersion());
        }

        $this->storage->upsert($event->getId()->getValue(), $document);

        var_dump($this->storage->find($event->getId()->getValue()));
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
     * @var \Apha\Domain\Identity
     */
    private $id;

    /**
     * @param \Apha\Domain\Identity $id
     * @return User
     */
    public static function create(\Apha\Domain\Identity $id) : self
    {
        $instance = new self($id);
        $instance->applyChange(new UserCreated($id));
        return $instance;
    }

    /**
     * @param \Apha\Domain\Identity $id
     */
    protected function __construct(\Apha\Domain\Identity $id)
    {
        $this->id = $id;
        parent::__construct();
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getId() : \Apha\Domain\Identity
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

/**
 * Read model of the User aggregate.
 */
class UserDocument implements \Apha\ReadStore\Document
{
    /**
     * @var string
     */
    private $identity;

    /**
     * @var int
     */
    private $version;

    /**
     * @param string $identity
     * @param int $version
     */
    public function __construct(string $identity, int $version)
    {
        $this->identity = $identity;
        $this->version = $version;
    }

    /**
     * @return array
     */
    public function serialize() : array
    {
    }

    /**
     * @param array $serialized
     * @return \Apha\ReadStore\Document
     */
    public function deserialize(array $serialized) : \Apha\ReadStore\Document
    {
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->identity;
    }

    /**
     * @return int
     */
    public function getVersion() : int
    {
        return $this->version;
    }

    /**
     * @param \Apha\Message\Event $event
     * @return void
     */
    public function apply(\Apha\Message\Event $event)
    {
        $this->version = $event->getVersion();
    }
}

// Read storage device where snapshots are stored
$readStorage = new \Apha\ReadStore\Storage\MemoryReadStorage();

// A new event bus with a mapping to specify what handlers to call for what event.
$eventBus = new \Apha\MessageBus\SimpleEventBus([
    UserCreated::class => [new UserCreatedHandler($readStorage)]
]);

// An event store to store events
$eventStore = new \Apha\EventStore\EventStore(
    $eventBus,
    new \Apha\EventStore\Storage\MemoryEventStorage(),
    \JMS\Serializer\SerializerBuilder::create()->build(),
    new \Apha\EventStore\EventClassMap([
        UserCreated::class
    ])
);

// A repository for objects of type Aggregate
$repository = new Repository($eventStore);

// A new command bus with a mapping to specify what handler to call for what command.
$commandBus = new \Apha\MessageBus\SimpleCommandBus([
    CreateUser::class => new CreateUserHandler($repository)
]);

// Send the command
$commandBus->send(new CreateUser(\Apha\Domain\Identity::createNew()));