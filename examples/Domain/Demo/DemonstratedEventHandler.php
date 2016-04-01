<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\Demo;

use Apha\Examples\Projections\DemoDocument;
use Apha\Message\Event;
use Apha\StateStore\Storage\StateStorage;

class DemonstratedEventHandler implements \Apha\EventHandling\EventHandler
{
    /**
     * @var StateStorage
     */
    private $readStorage;

    /**
     * @param StateStorage $readStorage
     */
    public function __construct(StateStorage $readStorage)
    {
        $this->readStorage = $readStorage;
    }

    /**
     * @param Event $event
     */
    public function on(Event $event)
    {
        try {
            $document = $this->readStorage->find($event->getIdentity()->getValue());
        } catch (\Apha\StateStore\Storage\DocumentNotFoundException $e) {
            $document = new DemoDocument($event->getIdentity()->getValue(), $event->getVersion());
        }

        $document->apply($event);
        $this->readStorage->upsert($event->getIdentity()->getValue(), $document);

        echo "Stored state version: {$event->getVersion()}\n";
    }
}
