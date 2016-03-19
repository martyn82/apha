<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\Demonstrate;

use Apha\EventHandling\EventHandler;
use Apha\Message\Event;
use Psr\Log\LoggerInterface;

class DemonstratedHandler implements EventHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Event $event
     */
    public function on(Event $event)
    {
        $eventName = $event->getEventName();
        $this->logger->info('Handle event', [
            'event' => $eventName,
            'handler' => get_class($this)
        ]);
    }
}
