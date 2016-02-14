<?php
declare(strict_types = 1);

namespace Apha\EventStore;

use Apha\Domain\Identity;

class AggregateNotFoundException extends \DomainException
{
    /**
     * @var string
     */
    private static $messageTemplate = "Aggregate with ID '%s' not found.";

    /**
     * @param Identity $aggregateId
     */
    public function __construct(Identity $aggregateId)
    {
        parent::__construct(
            sprintf(
                static::$messageTemplate,
                $aggregateId->getValue()
            )
        );
    }
}