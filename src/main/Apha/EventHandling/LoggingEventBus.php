<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;
use Psr\Log\LoggerInterface;

class LoggingEventBus extends EventBus
{
    /**
     * @var EventBus
     */
    private $eventBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EventBus $eventBus
     * @param LoggerInterface $logger
     */
    public function __construct(EventBus $eventBus, LoggerInterface $logger)
    {
        $this->eventBus = $eventBus;
        $this->logger = $logger;
    }

    /**
     * @param string $eventClass
     * @param EventHandler $handler
     */
    public function addHandler(string $eventClass, EventHandler $handler)
    {
        $this->eventBus->addHandler($eventClass, $handler);
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function publish(Event $event): bool
    {
        $this->logger->info('Dispatch event', [
            'event' => get_class($event),
            'bus' => get_class($this->eventBus)
        ]);

        if ($this->eventBus->publish($event)) {
            return true;
        }

        $this->logger->warning('Dead-letter message:event', [
            'event' => get_class($event),
            'bus' => get_class($this->eventBus)
        ]);

        return false;
    }
}