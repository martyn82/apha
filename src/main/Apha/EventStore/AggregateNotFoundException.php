<?php
declare(strict_types=1);

namespace Apha\EventStore;

use Ramsey\Uuid\UuidInterface;

class AggregateNotFoundException extends \DomainException
{
    /**
     * @var string
     */
    private static $messageTemplate = "Aggregate with ID '%s' not found.";

    /**
     * @param UuidInterface $aggregateId
     */
    public function __construct(UuidInterface $aggregateId)
    {
        parent::__construct(
            sprintf(
                static::$messageTemplate,
                $aggregateId->toString()
            )
        );
    }
}