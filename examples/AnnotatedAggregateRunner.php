#!/usr/bin/env php
<?php
declare(strict_types = 1);

namespace Apha\Examples;

use Apha\CommandHandling\CommandLogger;
use Apha\CommandHandling\Gateway\DefaultCommandGateway;
use Apha\CommandHandling\Interceptor\LoggingInterceptor;
use Apha\CommandHandling\SimpleCommandBus;
use Apha\Domain\GenericAggregateFactory;
use Apha\Domain\Identity;
use Apha\EventHandling\SimpleEventBus;
use Apha\EventStore\EventClassMap;
use Apha\EventStore\EventStore;
use Apha\EventStore\Storage\MemoryEventStorage;
use Apha\Examples\Annotations\ToDo\ToDoCommandHandler;
use Apha\Examples\Annotations\ToDo\ToDoItem;
use Apha\Examples\Annotations\ToDo\ToDoItemProjections;
use Apha\Examples\Domain\ToDo\CreateToDoItem;
use Apha\Examples\Domain\ToDo\DeadlineExpired;
use Apha\Examples\Domain\ToDo\MarkItemDone;
use Apha\Examples\Domain\ToDo\ToDoItemCreated;
use Apha\Examples\Domain\ToDo\ToDoItemDone;
use Apha\Repository\EventSourcingRepository;
use Apha\Serializer\JsonSerializer;
use Apha\StateStore\Storage\MemoryStateStorage;
use Monolog\Logger;

require_once __DIR__ . "/Runner.php";

/**
 * This example demonstrates the use of annotations to reduce the boiler plating of setting up an event-sourced system.
 */
class AnnotatedAggregateRunner extends Runner
{
    /**
     * @return void
     */
    public function run()
    {
        $logger = new Logger('default');
        $serializer = new JsonSerializer();

        $stateStorage = new MemoryStateStorage();
        $toDoItemProjections = new ToDoItemProjections($stateStorage);

        $eventStore = new EventStore(
            new SimpleEventBus([
                ToDoItemCreated::class => [$toDoItemProjections],
                ToDoItemDone::class => [$toDoItemProjections],
                DeadlineExpired::class => [$toDoItemProjections]
            ]),
            new MemoryEventStorage(),
            $serializer,
            new EventClassMap([
                ToDoItemCreated::class,
                ToDoItemDone::class,
                DeadlineExpired::class
            ])
        );
        $repository = new EventSourcingRepository(new GenericAggregateFactory(ToDoItem::class), $eventStore);
        $toDoItemCommandHandler = new ToDoCommandHandler($repository);

        $commandGateWay = new DefaultCommandGateway(
            new SimpleCommandBus([
                CreateToDoItem::class => $toDoItemCommandHandler,
                MarkItemDone::class => $toDoItemCommandHandler
            ]),
            [new LoggingInterceptor(new CommandLogger($logger))]
        );

        $toDoId = Identity::createNew();

        $commandGateWay->send(new CreateToDoItem($toDoId, "Wash the dishes", 5));

        $logger->debug(
            "Current state of ToDoItem", [
                'item' => $serializer->serialize($stateStorage->find($toDoId->getValue()))
            ]
        );

        $commandGateWay->send(new MarkItemDone($toDoId));

        $logger->debug(
            "Current state of ToDoItem", [
                'item' => $serializer->serialize($stateStorage->find($toDoId->getValue()))
            ]
        );
    }
}

exit(AnnotatedAggregateRunner::main());
