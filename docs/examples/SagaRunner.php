#!/usr/bin/env php
<?php
declare(strict_types = 1);
declare(ticks = 1);

namespace Apha\Examples;

use Apha\CommandHandling\Gateway\CommandGateway;
use Apha\CommandHandling\Gateway\DefaultCommandGateway;
use Apha\CommandHandling\Interceptor\LoggingCommandDispatchInterceptor;
use Apha\CommandHandling\SimpleCommandBus;
use Apha\Domain\GenericAggregateFactory;
use Apha\Domain\Identity;
use Apha\EventHandling\LoggingEventBus;
use Apha\EventHandling\SimpleEventBus;
use Apha\EventStore\EventClassMap;
use Apha\EventStore\EventStore;
use Apha\EventStore\Storage\MemoryEventStorage;
use Apha\Examples\Domain\ToDo\CreateToDoItem;
use Apha\Examples\Domain\ToDo\DeadlineExpired;
use Apha\Examples\Domain\ToDo\MarkItemDone;
use Apha\Examples\Domain\ToDo\ToDoCommandHandler;
use Apha\Examples\Domain\ToDo\ToDoItem;
use Apha\Examples\Domain\ToDo\ToDoItemCreated;
use Apha\Examples\Domain\ToDo\ToDoItemDone;
use Apha\Examples\Domain\ToDo\ToDoSaga;
use Apha\Repository\EventSourcingRepository;
use Apha\Saga\AssociationValues;
use Apha\Scheduling\SimpleEventScheduler;
use Apha\Serializer\JsonSerializer;
use Monolog\Logger;

require_once __DIR__ . "/Runner.php";

/**
 * This example demonstrates the use of a Saga to manage long running business processes.
 *
 * The domain is a ToDoList. The list is implicit, but the ToDoItems are the aggregates. The Saga keeps track of the
 * state of each ToDoItem and listens for events on ToDoItems.
 *
 * A ToDoItem is either created and when it is it can be marked as done or its deadline has expired (timeout).
 */
class SagaRunner extends Runner
{
    /**
     * @return void
     */
    public function run()
    {
        $logger = new Logger('default');
        $sagaId = Identity::createNew();

        $toDoSaga = new ToDoSaga($sagaId, new AssociationValues([]));

        $eventBus = new LoggingEventBus(
            new SimpleEventBus([
                ToDoItemCreated::class => [$toDoSaga],
                ToDoItemDone::class => [$toDoSaga],
                DeadlineExpired::class => [$toDoSaga]
            ]),
            $logger
        );

        $scheduler = new SimpleEventScheduler($eventBus);
        $toDoSaga->setEventScheduler($scheduler);

        $eventStore = new EventStore(
            $eventBus,
            new MemoryEventStorage(),
            new JsonSerializer(),
            new EventClassMap([
                ToDoItemCreated::class,
                ToDoItemDone::class,
                DeadlineExpired::class
            ])
        );

        $factory = new GenericAggregateFactory(ToDoItem::class);

        $repository = new EventSourcingRepository($factory, $eventStore);
        $commandHandler = new ToDoCommandHandler($repository);

        $commandBus = new SimpleCommandBus([
            CreateToDoItem::class => $commandHandler,
            MarkItemDone::class => $commandHandler
        ]);

        $loggingCommandInterceptor = new LoggingCommandDispatchInterceptor($logger);
        $commandGateway = new DefaultCommandGateway($commandBus, [$loggingCommandInterceptor]);

        // Send commands to create two todoitems
        $todoItemExpireSeconds = 3;
        $toCompleteId = Identity::createNew();
        $toExpireId = Identity::createNew();

        $commandGateway->send(new CreateToDoItem($toCompleteId, "Item to complete", $todoItemExpireSeconds));
        $commandGateway->send(new CreateToDoItem($toExpireId, "Item to expire", $todoItemExpireSeconds));

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

exit(SagaRunner::main());
