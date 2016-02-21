<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

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
     * @param Event $event
     * @return void
     */
    public function publish(Event $event)
    {
        $this->logger->info('Dispatch event', [
            'event' => get_class($event),
            'bus' => get_class($this->eventBus)
        ]);

        $this->eventBus->publish($event);

        $this->logger->info('Event dispatched', [
            'event' => get_class($event),
            'bus' => get_class($this->eventBus)
        ]);
    }
}