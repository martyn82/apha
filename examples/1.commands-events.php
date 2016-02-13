<?php
declare(strict_types = 1);
require_once dirname(__FILE__) . '/../vendor/autoload.php';

/*
 * This example demonstrates the basics of the command bus and event bus.
 *
 * A command is sent by the command bus and handled by a registered handler. Afterwards, an event is published to be
 * handled by registered handlers.
 */

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
$commandBus = new \Apha\MessageBus\SimpleCommandBus([
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