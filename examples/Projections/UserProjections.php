<?php
declare(strict_types = 1);

namespace Apha\Examples\Projections;

use Apha\EventHandling\EventHandler;
use Apha\Message\Event;
use Apha\StateStore\Storage\StateStorage;
use Psr\Log\LoggerInterface;

class UserProjections implements EventHandler
{
    /**
     * @var StateStorage
     */
    private $storage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StateStorage $storage
     * @param LoggerInterface $logger
     */
    public function __construct(StateStorage $storage, LoggerInterface $logger)
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
        $this->logger->info('Handle event', [
            'command' => $eventName,
            'handler' => get_class($this)
        ]);

        try {
            $document = $this->storage->find($event->getIdentity()->getValue());
            $document->apply($event);
        } catch (\Apha\StateStore\Storage\DocumentNotFoundException $e) {
            $document = new UserDocument($event->getIdentity()->getValue(), $event->getVersion());
        }

        $this->storage->upsert($event->getIdentity()->getValue(), $document);

        var_dump($this->storage->find($event->getIdentity()->getValue()));
    }
}