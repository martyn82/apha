<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\User;

use Apha\EventHandling\EventHandler;
use Apha\EventStore\Storage\EventStorage;
use Apha\Message\Event;
use Psr\Log\LoggerInterface;

class UserCreatedHandler implements EventHandler
{
    /**
     * @var EventStorage
     */
    private $storage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EventStorage $storage
     * @param LoggerInterface $logger
     */
    public function __construct(EventStorage $storage, LoggerInterface $logger)
    {
        $this->storage = $storage;
        $this->logger = $logger;
    }

    /**
     * @param Event $event
     */
    public function on(Event $event)
    {
        $eventName = $event->getEventName();
        $this->logger->info('Handling event', [
            'event' => $eventName,
            'handler' => get_class($this)
        ]);

        $events = $this->storage->find($event->getIdentity()->getValue());
        var_dump($events);
    }
}
