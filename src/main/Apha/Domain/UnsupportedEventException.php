<?php
declare(strict_types=1);

namespace Apha\Domain;

use Apha\Message\Event;

class UnsupportedEventException extends \DomainException
{
    /**
     * @var string
     */
    private static $messageTemplate = "Event '%s' is not supported for aggregate '%s'.";

    /**
     * @param Event $event
     * @param AggregateRoot $aggregateRoot
     */
    public function __construct(Event $event, AggregateRoot $aggregateRoot)
    {
        parent::__construct(
            sprintf(
                static::$messageTemplate,
                $event->getEventName(),
                get_class($aggregateRoot)
            )
        );
    }
}