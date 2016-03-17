<?php
declare(strict_types = 1);
declare(ticks = 1);

require_once __DIR__ . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);

use JMS\Serializer\Annotation as Serializer;

/*
 * This example demonstrates the use of a Saga to manage long running business processes.
 * The domain is a ToDoList. The list is implicit, but the ToDoItems are the aggregates. The Saga keeps track of the
 * state of each ToDoItem and listens for events on ToDoItems.
 * A ToDoItem is either created and when it is it can be marked as done or its deadline has expired (timeout).
 */

class ToDoSaga extends \Apha\Saga\Saga
{
    use \Apha\EventHandling\TypedEventHandler;

    /**
     * @var \Apha\Scheduling\EventScheduler
     */
    private $scheduler;

    /**
     * @var array
     */
    private $openItems;

    /**
     * @param \Apha\Domain\Identity $identity
     * @param \Apha\Saga\AssociationValues $associationValues
     */
    public function __construct(\Apha\Domain\Identity $identity, \Apha\Saga\AssociationValues $associationValues)
    {
        parent::__construct($identity, $associationValues);
        $this->openItems = [];
    }

    /**
     * @param \Apha\Scheduling\EventScheduler $eventScheduler
     */
    public function setEventScheduler(\Apha\Scheduling\EventScheduler $eventScheduler)
    {
        $this->scheduler = $eventScheduler;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * @param ToDoItemCreated $event
     */
    public function onToDoItemCreated(ToDoItemCreated $event)
    {
        if ($this->scheduler != null) {
            $token = $this->scheduler->scheduleAfter(
                new \DateInterval("PT{$event->getExpireSeconds()}S"),
                new DeadlineExpired($event->getIdentity())
            );
        } else {
            $token = null;
        }

        $this->openItems[$event->getIdentity()->getValue()] = [
            'description' => $event->getDescription(),
            'token' => $token
        ];
    }

    /**
     * @param ToDoItemDone $event
     */
    public function onToDoItemDone(ToDoItemDone $event)
    {
        $token = $this->openItems[$event->getIdentity()->getValue()]['token'];

        if ($token != null && $this->scheduler != null) {
            $this->scheduler->cancelSchedule($token);
        }

        unset($this->openItems[$event->getIdentity()->getValue()]);
    }

    /**
     * @param DeadlineExpired $event
     */
    public function onDeadlineExpired(DeadlineExpired $event)
    {
        unset($this->openItems[$event->getIdentity()->getValue()]);
    }
}

final class CreateToDoItem extends \Apha\Message\Command
{
    /**
     * @var \Apha\Domain\Identity
     */
    private $identity;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $expireSeconds;

    /**
     * @param \Apha\Domain\Identity $identity
     * @param string $description
     * @param int $expireSeconds
     */
    public function __construct(\Apha\Domain\Identity $identity, string $description, int $expireSeconds)
    {
        $this->identity = $identity;
        $this->description = $description;
        $this->expireSeconds = $expireSeconds;
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getIdentity(): \Apha\Domain\Identity
    {
        return $this->identity;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getExpireSeconds(): int
    {
        return $this->expireSeconds;
    }
}

final class ToDoItemCreated extends \Apha\Message\Event
{
    /**
     * @Serializer\Type("Apha\Domain\Identity")
     * @var \Apha\Domain\Identity
     */
    private $identity;

    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $description;

    /**
     * @Serializer\Type("integer")
     * @var int
     */
    private $expireSeconds;

    /**
     * @param \Apha\Domain\Identity $identity
     * @param string $description
     * @param int $expireSeconds
     */
    public function __construct(\Apha\Domain\Identity $identity, string $description, int $expireSeconds)
    {
        $this->identity = $identity;
        $this->description = $description;
        $this->expireSeconds = $expireSeconds;
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getIdentity(): \Apha\Domain\Identity
    {
        return $this->identity;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getExpireSeconds(): int
    {
        return $this->expireSeconds;
    }
}

final class MarkItemDone extends \Apha\Message\Command
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
    public function getIdentity()
    {
        return $this->identity;
    }
}

final class ToDoItemDone extends \Apha\Message\Event
{
    /**
     * @Serializer\Type("Apha\Domain\Identity")
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
    public function getIdentity()
    {
        return $this->identity;
    }
}

final class DeadlineExpired extends \Apha\Message\Event
{
    /**
     * @Serializer\Type("Apha\Domain\Identity")
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
    public function getIdentity()
    {
        return $this->identity;
    }
}

class ToDoItem extends \Apha\Domain\AggregateRoot
{
    /**
     * @var \Apha\Domain\Identity
     */
    private $identity;

    /**
     * @var bool
     */
    private $isDone;

    /**
     * @param CreateToDoItem $command
     * @return ToDoItem
     */
    public static function create(CreateToDoItem $command): self
    {
        $instance = new self();
        $instance->applyChange(
            new ToDoItemCreated($command->getIdentity(), $command->getDescription(), $command->getExpireSeconds())
        );
        return $instance;
    }

    /**
     */
    protected function __construct()
    {
        parent::__construct();
        $this->isDone = false;
    }

    /**
     * @param MarkItemDone $command
     */
    public function markDone(MarkItemDone $command)
    {
        $this->applyChange(new ToDoItemDone($command->getIdentity()));
    }

    /**
     * @return \Apha\Domain\Identity
     */
    public function getId(): \Apha\Domain\Identity
    {
        return $this->identity;
    }

    /**
     * @param ToDoItemCreated $event
     */
    public function applyToDoItemCreated(ToDoItemCreated $event)
    {
        $this->identity = $event->getIdentity();
    }

    /**
     * @param ToDoItemDone $event
     */
    public function applyToDoItemDone(ToDoItemDone $event)
    {
        $this->isDone = true;
    }
}

class ToDoCommandHandler implements \Apha\CommandHandling\CommandHandler
{
    use \Apha\CommandHandling\TypedCommandHandler;

    /**
     * @var \Apha\Repository\Repository
     */
    private $repository;

    /**
     * @param \Apha\Repository\Repository $repository
     */
    public function __construct(\Apha\Repository\Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CreateToDoItem $command
     */
    public function handleCreateToDoItem(CreateToDoItem $command)
    {
        $item = ToDoItem::create($command);
        $this->repository->store($item);
    }

    /**
     * @param MarkItemDone $command
     */
    public function handleMarkItemDone(MarkItemDone $command)
    {
        /* @var $item ToDoItem */
        $item = $this->repository->findById($command->getIdentity());
        $item->markDone($command);
        $this->repository->store($item);
    }
}

$logger = new \Monolog\Logger('default');
$sagaId = \Apha\Domain\Identity::createNew();

$toDoSaga = new ToDoSaga($sagaId, new \Apha\Saga\AssociationValues([]));

$eventBus = new \Apha\EventHandling\LoggingEventBus(
    new \Apha\EventHandling\SimpleEventBus([
        ToDoItemCreated::class => [$toDoSaga],
        ToDoItemDone::class => [$toDoSaga],
        DeadlineExpired::class => [$toDoSaga]
    ]),
    $logger
);

$scheduler = new \Apha\Scheduling\SimpleEventScheduler($eventBus);
$toDoSaga->setEventScheduler($scheduler);

$eventStore = new \Apha\EventStore\EventStore(
    $eventBus,
    new \Apha\EventStore\Storage\MemoryEventStorage(),
    new \Apha\Serializer\JsonSerializer(),
    new \Apha\EventStore\EventClassMap([
        ToDoItemCreated::class,
        ToDoItemDone::class,
        DeadlineExpired::class
    ])
);

$repository = new \Apha\Repository\EventSourcingRepository(ToDoItem::class, $eventStore);
$commandHandler = new ToDoCommandHandler($repository);

$commandBus = new \Apha\CommandHandling\SimpleCommandBus([
    CreateToDoItem::class => $commandHandler,
    MarkItemDone::class => $commandHandler
]);

$loggingCommandInterceptor = new \Apha\CommandHandling\Interceptor\LoggingCommandDispatchInterceptor($logger);
$commandGateway = new \Apha\CommandHandling\Gateway\DefaultCommandGateway($commandBus, [$loggingCommandInterceptor]);

// Send commands to create two todoitems
$todoItemExpireSeconds = 3;
$toCompleteId = \Apha\Domain\Identity::createNew();
$toExpireId = \Apha\Domain\Identity::createNew();

$commandGateway->send(new CreateToDoItem($toCompleteId, "Item to complete", $todoItemExpireSeconds));
$commandGateway->send(new CreateToDoItem($toExpireId, "Item to expire", $todoItemExpireSeconds));

// Start an idling process for 5 seconds and wait for the ToDoItem to expire
$timeout = 5;
$timePassed = 0;
$startTime = time();

$itemDone = false;
while ($timePassed < $timeout) {
    $timePassed = time() - $startTime;

    // One todoitem will be completed after 1 second
    if ($timePassed === 1 && !$itemDone) {
        $itemDone = true;
        $commandGateway->send(new MarkItemDone($toCompleteId));
    }
}
