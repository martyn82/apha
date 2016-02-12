<?php
declare(strict_types=1);

namespace Apha\Domain;

use Apha\Domain\Message\Event;
use Apha\Domain\Message\Events;
use Ramsey\Uuid\UuidInterface;

abstract class AggregateRoot
{
    /**
     * @var Events
     */
    private $changes;

    /**
     */
    protected function __construct()
    {
        $this->changes = new Events();
    }

    /**
     * @return UuidInterface
     */
    abstract public function getId() : UuidInterface;

    /**
     * @return Events
     */
    public function getUncommittedChanges() : Events
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