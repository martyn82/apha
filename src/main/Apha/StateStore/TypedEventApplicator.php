<?php
declare(strict_types = 1);

namespace Apha\StateStore;

use Apha\Message\Event;

trait TypedEventApplicator
{
    /**
     * @param Event $event
     */
    public function apply(Event $event)
    {
        $this->applyByInflection($event);
    }

    /**
     * @param Event $event
     * @throws \InvalidArgumentException
     */
    private function applyByInflection(Event $event)
    {
        $eventApplyMethod = 'apply' . $event->getEventName();

        if (!method_exists($this, $eventApplyMethod)) {
            $eventClassName = get_class($event);
            throw new \InvalidArgumentException("Unable to apply event '{$eventClassName}'.");
        }

        $this->{$eventApplyMethod}($event);
    }
}