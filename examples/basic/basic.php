<?php
declare(strict_types = 1);
require_once dirname(__FILE__) . '/../../vendor/autoload.php';

/**
 * Demonstrate command class.
 */
final class Demonstrate implements \Apha\Message\Command {}

/**
 * Demonstrated event class.
 */
final class Demonstrated extends \Apha\Message\Event {}

/**
 * Handler for Demonstrate command.
 */
class DemonstrateHandler implements \Apha\MessageHandler\CommandHandler
{
    /**
     * @param \Apha\Message\Command $command
     */
    public function handle(\Apha\Message\Command $command)
    {
        $commandName = get_class($command);
        echo "Handling command: '{$commandName}'.\n";
    }
}

/**
 * Handler for Demonstrated event.
 */
class DemonstratedHandler implements \Apha\MessageHandler\EventHandler
{
    /**
     * @param \Apha\Message\Event $event
     */
    public function on(\Apha\Message\Event $event)
    {
        $eventName = $event->getEventName();
        echo "Handling event: '{$eventName}'.\n";
    }
}

// A new command bus with a mapping to specify what handler to call for what command.
$commandBus = new \Apha\MessageBus\CommandBus([
    Demonstrate::class => new DemonstrateHandler()
]);

// A new event bus with a mapping to specify what handlers to call for what event.
$eventBus = new \Apha\MessageBus\EventBus([
    Demonstrated::class => [new DemonstratedHandler()]
]);

// Send the command
$commandBus->send(new Demonstrate());

// Publish the event (the lack of an exception means success)
$eventBus->publish(new Demonstrated());