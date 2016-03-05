<?php
declare(strict_types = 1);
require_once __DIR__ . '/../vendor/autoload.php';

\Doctrine\Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation', __DIR__ . '/../vendor/jms/serializer/src'
);

/*
 * This example demonstrates the basics of the command bus and event bus.
 *
 * A command is sent by the command bus and handled by a registered handler. Afterwards, an event is published to be
 * handled by registered handlers.
 */

/**
 * Demonstrate command class.
 */
final class Demonstrate extends \Apha\Message\Command
{
}

/**
 * Demonstrated event class.
 */
final class Demonstrated extends \Apha\Message\Event
{
}

/**
 * Handler for Demonstrate command.
 */
class DemonstrateHandler implements \Apha\CommandHandling\CommandHandler
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
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
    }
}

/**
 * Handler for Demonstrated event.
 */
class DemonstratedHandler implements \Apha\EventHandling\EventHandler
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param \Apha\Message\Event $event
     */
    public function on(\Apha\Message\Event $event)
    {
        $eventName = $event->getEventName();
        $this->logger->info('Handle event', [
            'event' => $eventName,
            'handler' => get_class($this)
        ]);
    }
}

$logger = new \Monolog\Logger('default');

// A new command bus with a mapping to specify what handler to call for what command.
$commandBus = new \Apha\CommandHandling\SimpleCommandBus([
    Demonstrate::class => new DemonstrateHandler($logger)
]);

// A new event bus with a mapping to specify what handlers to call for what event.
$eventBus = new \Apha\EventHandling\SimpleEventBus([
    Demonstrated::class => [new DemonstratedHandler($logger)]
]);

$loggingCommandInterceptor = new \Apha\CommandHandling\Interceptor\LoggingCommandDispatchInterceptor($logger);

$commandGateway = new \Apha\CommandHandling\Gateway\DefaultCommandGateway($commandBus, [$loggingCommandInterceptor]);
$loggingEventBus = new \Apha\EventHandling\LoggingEventBus($eventBus, $logger);

// Send the command
$commandGateway->send(new Demonstrate());

// Publish the event (the lack of an exception means success)
$loggingEventBus->publish(new Demonstrated());