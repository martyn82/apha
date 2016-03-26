<?php
//declare(strict_types = 1);
//
//namespace Apha\Domain;
//
//use Apha\Annotations\Annotation\AggregateIdentifier;
//use Apha\Annotations\Annotation\CommandHandler;
//use Apha\Annotations\Annotation\EventHandler;
//use Apha\CommandHandling\AnnotatedCommandHandler;
//use Apha\CommandHandling\SimpleCommandBus;
//use Apha\EventHandling\AnnotatedEventHandler;
//use Apha\EventHandling\SimpleEventBus;
//use Apha\EventStore\EventClassMap;
//use Apha\EventStore\EventStore;
//use Apha\EventStore\Storage\MemoryEventStorage;
//use Apha\Message\Command;
//use Apha\Message\Event;
//use Apha\Repository\EventSourcingRepository;
//use Apha\Repository\Repository;
//use Apha\Serializer\JsonSerializer;
//
///**
// * @group wip
// */
//class AnnotatedAggregateRootTest extends \PHPUnit_Framework_TestCase
//{
//    /**
//     * @test
//     */
//    public function annotations()
//    {
//        $eventStore = new EventStore(
//            new SimpleEventBus([
//                Event::class => [new ToDoItemEventHandler()]
//            ]),
//            new MemoryEventStorage(),
//            new JsonSerializer(),
//            new EventClassMap([
//                ToDoItemCreated::class
//            ])
//        );
//        $repository = new EventSourcingRepository(new GenericAggregateFactory(ToDoItem::class), $eventStore);
//
//        $commandBus = new SimpleCommandBus([
//            CreateToDoItem::class => new ToDoItemCommandHandler($repository)
//        ]);
//
//        $commandBus->send(new CreateToDoItem(Identity::createNew(), "New item"));
//    }
//}
//
//class ToDoItemEventHandler implements \Apha\EventHandling\EventHandler
//{
//    use AnnotatedEventHandler;
//
//    /**
//     * @EventHandler()
//     * @param ToDoItemCreated $event
//     */
//    public function onToDoItemCreated(ToDoItemCreated $event)
//    {
//
//    }
//}
//
//class ToDoItemCommandHandler implements \Apha\CommandHandling\CommandHandler
//{
//    use AnnotatedCommandHandler;
//
//    /**
//     * @var Repository
//     */
//    private $repository;
//
//    /**
//     * @param Repository $repository
//     */
//    public function __construct(Repository $repository)
//    {
//        $this->repository = $repository;
//    }
//
//    /**
//     * @CommandHandler()
//     * @param CreateToDoItem $command
//     */
//    public function handleCreateToDoItem(CreateToDoItem $command)
//    {
//        $aggregate = ToDoItem::create($command);
//        $this->repository->store($aggregate);
//    }
//}
//
//class ToDoItem extends AnnotatedAggregateRoot
//{
//    /**
//     * @AggregateIdentifier("Apha\Domain\Identity")
//     * @var Identity
//     */
//    private $identity;
//
//    /**
//     * @return Identity
//     */
//    public function getId(): Identity
//    {
//        return $this->identity;
//    }
//
//    /**
//     * @CommandHandler()
//     * @param CreateToDoItem $command
//     * @return ToDoItem
//     */
//    public static function create(CreateToDoItem $command): self
//    {
//        $instance = new self();
//        $instance->applyChange(new ToDoItemCreated($command->getIdentity(), $command->getDescription()));
//        return $instance;
//    }
//
//    /**
//     * @param ToDoItemCreated $event
//     */
//    protected function applyToDoItemCreated(ToDoItemCreated $event)
//    {
//        $this->identity = $event->getIdentity();
//    }
//}
//
//final class CreateToDoItem extends Command
//{
//    /**
//     * @var Identity
//     */
//    private $identity;
//
//    /**
//     * @var string
//     */
//    private $description;
//
//    /**
//     * @param Identity $identity
//     * @param string $description
//     */
//    public function __construct(Identity $identity, string $description)
//    {
//        $this->identity = $identity;
//        $this->description = $description;
//    }
//
//    /**
//     * @return Identity
//     */
//    public function getIdentity()
//    {
//        return $this->identity;
//    }
//
//    /**
//     * @return string
//     */
//    public function getDescription()
//    {
//        return $this->description;
//    }
//}
//
//final class ToDoItemCreated extends Event
//{
//    /**
//     * @var string
//     */
//    private $description;
//
//    /**
//     * @param Identity $identity
//     * @param string $description
//     */
//    public function __construct(Identity $identity, $description)
//    {
//        $this->setIdentity($identity);
//        $this->description = $description;
//    }
//
//    /**
//     * @return string
//     */
//    public function getDescription()
//    {
//        return $this->description;
//    }
//}
