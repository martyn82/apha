#!/usr/bin/env php
<?php
declare(strict_types = 1);

namespace Apha\Examples;

use Apha\CommandHandling\CommandLogger;
use Apha\CommandHandling\Gateway\DefaultCommandGateway;
use Apha\CommandHandling\Interceptor\LoggingInterceptor;
use Apha\CommandHandling\SimpleCommandBus;
use Apha\EventHandling\EventLogger;
use Apha\EventHandling\SimpleEventBus;
use Apha\Examples\Domain\Demonstrate\Demonstrate;
use Apha\Examples\Domain\Demonstrate\Demonstrated;
use Apha\Examples\Domain\Demonstrate\DemonstratedHandler;
use Apha\Examples\Domain\Demonstrate\DemonstrateHandler;
use Monolog\Logger;

require_once __DIR__ . '/Runner.php';

/**
 * This example demonstrates the basics of the command bus and event bus.
 *
 * A command is sent by the command bus and handled by a registered handler. Afterwards, an event is published to be
 * handled by registered handlers.
 */
class CommandsEventsRunner extends Runner
{
    /**
     * @return void
     */
    public function run()
    {
        $logger = new Logger('default');

        // Create new command bus with a mapping to specify what handler to call for what command.
        $commandBus = new SimpleCommandBus([
            Demonstrate::class => new DemonstrateHandler($logger)
        ]);

        // A new event bus with a mapping to specify what handlers to call for what event.
        $eventBus = new SimpleEventBus([
            Demonstrated::class => [new DemonstratedHandler($logger)]
        ]);

        $loggingCommandInterceptor = new LoggingInterceptor(new CommandLogger($logger));

        $commandGateway = new DefaultCommandGateway($commandBus, [$loggingCommandInterceptor]);
        $eventBus->setLogger(new EventLogger($logger));

        // Send the command
        $commandGateway->send(new Demonstrate());

        // Publish the event
        $eventBus->publish(new Demonstrated());
    }
}

exit(CommandsEventsRunner::main());
