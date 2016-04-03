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
use Apha\Repository\EventSourcingRepository;
use Apha\Serializer\JsonSerializer;
use Monolog\Logger;

require_once __DIR__ . "/Runner.php";

/**
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
class AggregateEventStoreRunner extends Runner
{
    /**
     * @return void
     */
    public function run()
    {
        $logger = new Logger('default');

        // The storage device to store event data
        $eventStorage = new MemoryEventStorage();

        // A new event bus with a mapping to specify what handlers to call for what event.
        $eventBus = new SimpleEventBus([
            UserCreated::class => [new UserCreatedHandler($eventStorage, $logger)]
        ]);

        $loggingEventBus = new LoggingEventBus($eventBus, $logger);

        // An event store to store events
        $eventStore = new EventStore(
            $loggingEventBus,
            $eventStorage,
            new JsonSerializer(),
            new EventClassMap([
                UserCreated::class
            ])
        );

        // A basic aggregate factory to be able to reconstruct the aggregate
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

exit(AggregateEventStoreRunner::main());
