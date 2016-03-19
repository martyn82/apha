<?php
declare(strict_types = 1);
require_once __DIR__ . '/../../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../../vendor/jms/serializer/src'
);

/*
 * This example demonstrates the use of a StateStore to store documents. Documents are the snapshots of aggregates after
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
final class CreateUser extends \Apha\Message\Command
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
     * @param \Apha\Domain\Identity $id
     */
    public function __construct(\Apha\Domain\Identity $id)
    {
        $this->setIdentity($id);
    }
}

/**
 * Handler for CreateUser command.
 */
class CreateUserHandler implements \Apha\CommandHandling\CommandHandler
{
    /**
     * @var \Apha\Repository\Repository
     */
    private $repository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Apha\Repository\Repository $repository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Apha\Repository\Repository $repository, \Psr\Log\LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    /**
     * @param \Apha\Message\Command $command
     */
    public function handle(\Apha\Message\Command $command)
    {
        $commandName = get_class($command);
        $this->logger->info('Handle command', [
            'command' => $commandName,
            'handler' => get_class($this)
        ]);

        $user = User::create($command->getId());
        $this->repository->store($user);
    }
}

/**
 * Handler for UserCreated event.
 */
class UserCreatedHandler implements \Apha\EventHandling\EventHandler
{
    /**
     * @var \Apha\StateStore\Storage\MemoryStateStorage
     */
    private $storage;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Apha\StateStore\Storage\MemoryStateStorage $storage
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Apha\StateStore\Storage\MemoryStateStorage $storage, \Psr\Log\LoggerInterface $logger)
    {
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * @param \Apha\Message\Event $event
     */
    public function on(\Apha\Message\Event $event)
    {
        $eventName = $event->getEventName();
        $this->logger->info('Handle event', [
            'command' => $eventName,
            'handler' => get_class($this)
        ]);

        try {
            $document = $this->storage->find($event->getIdentity()->getValue());
            $document->apply($event);
        } catch (\Apha\StateStore\Storage\DocumentNotFoundException $e) {
            $document = new UserDocument($event->getIdentity()->getValue(), $event->getVersion());
        }

        $this->storage->upsert($event->getIdentity()->getValue(), $document);

        var_dump($this->storage->find($event->getIdentity()->getValue()));
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
class UserDocument implements \Apha\StateStore\Document
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
     * @return \Apha\StateStore\Document
     */
    public function deserialize(array $serialized) : \Apha\StateStore\Document
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

$logger = new \Monolog\Logger('default');

// Read storage device where snapshots are stored
$readStorage = new \Apha\StateStore\Storage\MemoryStateStorage();

// A new event bus with a mapping to specify what handlers to call for what event.
$eventBus = new \Apha\EventHandling\LoggingEventBus(
    new \Apha\EventHandling\SimpleEventBus([
        UserCreated::class => [new UserCreatedHandler($readStorage, $logger)]
    ]),
    $logger
);

// An event store to store events
$eventStore = new \Apha\EventStore\EventStore(
    $eventBus,
    new \Apha\EventStore\Storage\MemoryEventStorage(),
    new \Apha\Serializer\JsonSerializer(),
    new \Apha\EventStore\EventClassMap([
        UserCreated::class
    ])
);

// A repository for objects of type Aggregate
$repository = new \Apha\Repository\EventSourcingRepository(User::class, $eventStore);

// A new command bus with a mapping to specify what handler to call for what command.
$commandBus = new \Apha\CommandHandling\SimpleCommandBus([
    CreateUser::class => new CreateUserHandler($repository, $logger)
]);
$loggingCommandInterceptor = new \Apha\CommandHandling\Interceptor\LoggingCommandDispatchInterceptor($logger);
$commandGateway = new \Apha\CommandHandling\Gateway\DefaultCommandGateway($commandBus, [$loggingCommandInterceptor]);

// Send the command
$commandGateway->send(new CreateUser(\Apha\Domain\Identity::createNew()));