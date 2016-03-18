<?php
declare(strict_types = 1);
require_once __DIR__ . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);

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
final class CreateUser extends \Apha\Message\Command
{
    /**
     * @var \Apha\Domain\Identity
     */
    private $identity;

    /**
     * @param \Apha\Domain\Identity $identity
     */
    public function __construct(\Apha\Domain\Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getId() : \Apha\Domain\Identity
    {
        return $this->identity;
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
     * @param \Psr\Log\LoggerInterface
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
     * @var \Apha\EventStore\Storage\EventStorage
     */
    private $storage;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Apha\EventStore\Storage\EventStorage $storage
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Apha\EventStore\Storage\EventStorage $storage, \Psr\Log\LoggerInterface $logger)
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
        $this->logger->info('Handling event', [
            'event' => $eventName,
            'handler' => get_class($this)
        ]);

        $events = $this->storage->find($event->getIdentity()->getValue());
        var_dump($events);
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

$logger = new \Monolog\Logger('default');

// The storage device to store event data
$eventStorage = new \Apha\EventStore\Storage\MemoryEventStorage();

// A new event bus with a mapping to specify what handlers to call for what event.
$eventBus = new \Apha\EventHandling\SimpleEventBus([
    UserCreated::class => [new UserCreatedHandler($eventStorage, $logger)]
]);

$loggingEventBus = new \Apha\EventHandling\LoggingEventBus($eventBus, $logger);

// An event store to store events
$eventStore = new \Apha\EventStore\EventStore(
    $loggingEventBus,
    $eventStorage,
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