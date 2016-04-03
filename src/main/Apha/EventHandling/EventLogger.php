<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Message\Event;
use Apha\Serializer\ArrayConverter;
use Psr\Log\LoggerInterface;

class EventLogger implements EventDispatchHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ArrayConverter
     */
    private $converter;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->converter = new ArrayConverter();
    }

    /**
     * @param Event $event
     */
    public function onBeforeDispatch(Event $event)
    {
        $this->logger->info('Dispatch event', [
            'type' => get_class($event),
            'event' => $this->converter->objectToArray($event)
        ]);
    }

    /**
     * @param Event $event
     */
    public function onDispatchSuccessful(Event $event)
    {
        $this->logger->info('Event successfully dispatched.', [
            'type' => get_class($event),
            'event' => $this->converter->objectToArray($event)
        ]);
    }

    /**
     * @param Event $event
     */
    public function onDispatchFailed(Event $event)
    {
        $this->logger->warning('Dead letter event encountered', [
            'type' => get_class($event),
            'event' => $this->converter->objectToArray($event)
        ]);
    }
}
