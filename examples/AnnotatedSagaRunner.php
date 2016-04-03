#!/usr/bin/env php
<?php
declare(strict_types = 1);
declare(ticks = 1);

namespace Apha\Examples;

use Apha\Annotations\DefaultParameterResolver;
use Apha\CommandHandling\Gateway\CommandGateway;
use Apha\CommandHandling\Gateway\DefaultCommandGateway;
use Apha\CommandHandling\Interceptor\LoggingInterceptor;
use Apha\CommandHandling\SimpleCommandBus;
use Apha\Domain\GenericAggregateFactory;
use Apha\Domain\Identity;
use Apha\EventHandling\LoggingEventBus;
use Apha\EventHandling\SimpleEventBus;
use Apha\EventStore\EventClassMap;
use Apha\EventStore\EventStore;
use Apha\EventStore\Storage\MemoryEventStorage;
use Apha\Examples\Annotations\ToDo\ToDoCommandHandler;
use Apha\Examples\Annotations\ToDo\ToDoItem;
use Apha\Examples\Annotations\ToDo\ToDoSaga;
use Apha\Examples\Annotations\ToDo\ToDoSagaFactory;
use Apha\Examples\Domain\ToDo\CreateToDoItem;
use Apha\Examples\Domain\ToDo\DeadlineExpired;
use Apha\Examples\Domain\ToDo\MarkItemDone;
use Apha\Examples\Domain\ToDo\ToDoItemCreated;
use Apha\Examples\Domain\ToDo\ToDoItemDone;
use Apha\Message\Event;
use Apha\Repository\EventSourcingRepository;
use Apha\Saga\SagaRepository;
use Apha\Saga\SagaSerializer;
use Apha\Saga\SimpleAssociationValueResolver;
use Apha\Saga\SimpleSagaManager;
use Apha\Saga\Storage\MemorySagaStorage;
use Apha\Scheduling\SimpleEventScheduler;
use Apha\Serializer\JsonSerializer;
use Monolog\Logger;

require_once __DIR__ . "/Runner.php";

/**
 * This example demonstrates the use of annotated saga and saga manager to handle long running business processes.
 */
class AnnotatedSagaRunner extends Runner
{
    /**
     * @return void
     */
    public function run()
    {
        $logger = new Logger('default');

        $eventBus = new LoggingEventBus(
            new SimpleEventBus(),
            $logger
        );

        $scheduler = new SimpleEventScheduler($eventBus);
        $parameterResolver = new DefaultParameterResolver();
        $factory = new ToDoSagaFactory($scheduler, $parameterResolver, $logger);
        $serializer = new JsonSerializer();

        $sagaManager = new SimpleSagaManager(
            [ToDoSaga::class],
            new SagaRepository(
                new MemorySagaStorage(),
                new SagaSerializer($serializer, $factory)
            ),
            new SimpleAssociationValueResolver(),
            $factory
        );

        $eventBus->addHandler(Event::class, $sagaManager);

        $eventStore = new EventStore(
            $eventBus,
            new MemoryEventStorage(),
            $serializer,
            new EventClassMap([
                ToDoItemCreated::class,
                ToDoItemDone::class,
                DeadlineExpired::class
            ])
        );

        $commandHandler = new ToDoCommandHandler(
            new EventSourcingRepository(
                new GenericAggregateFactory(ToDoItem::class),
                $eventStore
            )
        );

        $commandBus = new SimpleCommandBus([
            CreateToDoItem::class => $commandHandler,
            MarkItemDone::class => $commandHandler
        ]);

        $commandGateway = new DefaultCommandGateway($commandBus, [new LoggingInterceptor($logger)]);

        // Send commands to create two todoitems
        $todoItemExpireSeconds = 3;
        $toCompleteId = Identity::createNew();

        $commandGateway->send(new CreateToDoItem($toCompleteId, "Item to complete", $todoItemExpireSeconds));
        $commandGateway->send(new CreateToDoItem(Identity::createNew(), "Item to expire", $todoItemExpireSeconds));

        // Start an idling process for 5 seconds and wait for the ToDoItem to expire
        $this->idle(5, $commandGateway, $toCompleteId);
    }

    /**
     * @param int $timeout
     * @param CommandGateway $commandGateway
     * @param Identity $toCompleteId
     */
    private function idle(int $timeout, CommandGateway $commandGateway, Identity $toCompleteId)
    {
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
    }
}

exit(AnnotatedSagaRunner::main());
