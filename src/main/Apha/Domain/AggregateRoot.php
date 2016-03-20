<?php
declare(strict_types = 1);

namespace Apha\Domain;

use Apha\Message\Event;
use Apha\Message\Events;

abstract class AggregateRoot
{
    /**
     * @var Events
     */
    private $changes;

    /**
     * @var int
     */
    private $version;

    /**
     * @param Events $events
     * @return AggregateRoot
     */
    public static function reconstruct(Events $events): self
    {
        $instance = new static();
        $instance->loadFromHistory($events);
        return $instance;
    }

    /**
     */
    protected function __construct()
    {
        $this->changes = new Events();
        $this->version = -1;
    }

    /**
     * @return Identity
     */
    abstract public function getId(): Identity;

    /**
     * @return int
     */
    final public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return Events
     */
    public function getUncommittedChanges(): Events
    {
        return $this->changes;
    }

    /**
     */
    public function markChangesCommitted()
    {
        $this->changes->clear();
    }

    /**
     * @param Events $history
     */
    public function loadFromHistory(Events $history)
    {
        foreach ($history->getIterator() as $event) {
            $this->internalApplyChange($event, false);
        }

        $historyCopy = $history->getArrayCopy();

        /* @var $lastEvent Event */
        $lastEvent = end($historyCopy);
        $this->version = $lastEvent->getVersion();
    }

    /**
     * @param Event $event
     * @throws UnsupportedEventException
     */
    protected function apply(Event $event)
    {
        $applyMethod = 'apply' . $event->getEventName();

        if (!method_exists($this, $applyMethod)) {
            throw new UnsupportedEventException($event, $this);
        }

        static::$applyMethod($event);
    }

    /**
     * @param Event $event
     */
    protected function applyChange(Event $event)
    {
        $this->internalApplyChange($event, true);
    }

    /**
     * @param Event $event
     * @param bool $isNew
     * @throws UnsupportedEventException
     */
    private function internalApplyChange(Event $event, bool $isNew)
    {
        $this->apply($event);

        if ($isNew) {
            $this->changes->add($event);
        }
    }
}