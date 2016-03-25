#!/usr/bin/env php
<?php
declare(strict_types = 1);

namespace Apha\Examples;

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
use Apha\Message\Event;
use Apha\Repository\EventSourcingRepository;
use Apha\Saga\AssociationValue;
use Apha\Saga\GenericSagaFactory;
use Apha\Saga\SagaRepository;
use Apha\Saga\SimpleAssociationValueResolver;
use Apha\Saga\SimpleSagaManager;
use Apha\Saga\Storage\MemorySagaStorage;
use Apha\Serializer\JsonSerializer;
use Monolog\Logger;

require_once __DIR__ . "/Runner.php";

/**
 * This example demonstrates the use of a SagaManager to manage your sagas.
 *
 * In this example the ToDoSaga is persisted after an event of interest has been processed and the Saga is removed when
 * the long running process has finished. To illustrate this, a debug message is logged at the end of the runner.
 */
class ManagedSagaRunner extends Runner
{
    /**
     * @return void
     */
    public function run()
    {
        $logger = new Logger('default');

        $sagaFactory = new GenericSagaFactory();
        $sagaStorage = new MemorySagaStorage();
        $sagaRepository = new SagaRepository(
            $sagaStorage,
            new JsonSerializer()
        );

        $resolver = new SimpleAssociationValueResolver();
        $sagaManager = new SimpleSagaManager([ToDoSaga::class], $sagaRepository, $resolver, $sagaFactory);

        $eventBus = new LoggingEventBus(
            new SimpleEventBus([]),
            $logger
        );

        $eventBus->addHandler(Event::class, $sagaManager);

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

        $toCompleteId = Identity::createNew();
        $commandGateway->send(new CreateToDoItem($toCompleteId, "Item to complete", PHP_INT_MAX));

        $sagaIds = $sagaRepository->find(ToDoSaga::class, new AssociationValue('identity', $toCompleteId->getValue()));
        $logger->debug("Active sagas found:", [
            'count' => count($sagaIds),
            'saga' => !empty($sagaIds) ? $sagaStorage->findById($sagaIds[0]->getValue())['serialized'] : ''
        ]);

        $commandGateway->send(new MarkItemDone($toCompleteId));

        $sagaIds = $sagaRepository->find(ToDoSaga::class, new AssociationValue('identity', $toCompleteId->getValue()));
        $logger->debug("Active sagas found:", [
            'count' => count($sagaIds),
            'saga' => !empty($sagaIds) ? $sagaStorage->findById($sagaIds[0]->getValue())['serialized'] : ''
        ]);
    }
}

exit(ManagedSagaRunner::main());
