<?php
declare(strict_types = 1);

namespace Apha\Domain\Annotation;

use Apha\Annotations\AnnotationReaderException;

class NoAggregateIdentifierException extends AnnotationReaderException
{
    /**
     * @var string
     */
    private static $messageTemplate = "AggregateRoot '%s' has no aggregate identifier.";

    /**
     * @param AnnotatedAggregateRoot $aggregateRoot
     */
    public function __construct(AnnotatedAggregateRoot $aggregateRoot)
    {
        parent::__construct(
            sprintf(static::$messageTemplate, get_class($aggregateRoot))
        );
    }
}