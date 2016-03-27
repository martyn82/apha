<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
final class AggregateIdentifier extends Annotation
{
    /**
     * @var string
     */
    public $propertyName;

    /**
     * @var string
     */
    public $type;
}