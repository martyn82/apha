<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;
use Apha\Serializer\ArrayConverter;
use Apha\Serializer\JsonSerializer;
use Apha\Serializer\Serializer;
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
     * @var ArrayConverter
     */
    private $converter;

    /**
     * @param EventBus $eventBus
     * @param LoggerInterface $logger
     */
    public function __construct(EventBus $eventBus, LoggerInterface $logger)
    {
        $this->eventBus = $eventBus;
        $this->logger = $logger;
        $this->converter = new ArrayConverter();
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
     * @param string $eventClass
     * @param EventHandler $handler
     */
    public function removeHandler(string $eventClass, EventHandler $handler)
    {
        $this->eventBus->removeHandler($eventClass, $handler);
    }

    /**
     * @param Event $event
     * @return bool
     */
    public function publish(Event $event): bool
    {
        $this->logger->info('Dispatch event', [
            'type' => get_class($event),
            'event' => $this->converter->objectToArray($event),
            'bus' => get_class($this->eventBus)
        ]);

        if ($this->eventBus->publish($event)) {
            return true;
        }

        $this->logger->warning('Dead-letter event', [
            'type' => get_class($event),
            'event' => $this->converter->objectToArray($event),
            'bus' => get_class($this->eventBus)
        ]);

        return false;
    }
}