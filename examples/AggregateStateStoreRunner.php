#!/usr/bin/env php
<?php
declare(strict_types = 1);

namespace Apha\Examples;

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
use Apha\Examples\Domain\User\CreateUser;
use Apha\Examples\Domain\User\CreateUserHandler;
use Apha\Examples\Domain\User\User;
use Apha\Examples\Domain\User\UserCreated;
use Apha\Examples\Domain\User\UserCreatedHandler;
use Apha\Examples\Projections\UserProjections;
use Apha\Repository\EventSourcingRepository;
use Apha\Serializer\JsonSerializer;
use Apha\StateStore\Storage\MemoryStateStorage;
use Monolog\Logger;

require_once __DIR__ . "/Runner.php";

/**
 * This example demonstrates the use of a StateStore to store documents. Documents are the snapshots of aggregates after
 * their history of events have been applied.
 *
 * Whenever an event on an aggregate is published, the readstore is updated by a handler.
 *
 * In this case, two UserCreated handlers are registered. After updating the read store it prints the contents of the
 * document to show how the documents are stored.
 */
class AggregateStateStoreRunner extends Runner
{
    /**
     * @return void
     */
    public function run()
    {
        $logger = new Logger('default');

        // Read storage device where snapshots are stored
        $readStorage = new MemoryStateStorage();

        // Event storage device to store events
        $eventStorage = new MemoryEventStorage();

        // A new event bus with a mapping to specify what handlers to call for what event.
        $eventBus = new LoggingEventBus(
            new SimpleEventBus([
                UserCreated::class => [
                    new UserCreatedHandler($eventStorage, $logger),
                    new UserProjections($readStorage, $logger)
                ]
            ]),
            $logger
        );

        // An event store to store events
        $eventStore = new EventStore(
            $eventBus,
            $eventStorage,
            new JsonSerializer(),
            new EventClassMap([
                UserCreated::class
            ])
        );

        $factory = new GenericAggregateFactory(User::class);

        // A repository for objects of type Aggregate
        $repository = new EventSourcingRepository($factory, $eventStore);

        // A new command bus with a mapping to specify what handler to call for what command.
        $commandBus = new SimpleCommandBus([
            CreateUser::class => new CreateUserHandler($repository, $logger)
        ]);
        $loggingCommandInterceptor = new LoggingInterceptor($logger);
        $commandGateway = new DefaultCommandGateway($commandBus, [$loggingCommandInterceptor]);

        // Send the command
        $commandGateway->send(new CreateUser(Identity::createNew()));
    }
}

exit(AggregateStateStoreRunner::main());
