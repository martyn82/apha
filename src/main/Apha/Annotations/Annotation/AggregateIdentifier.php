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

    /**
     * @param array $parameters
     * @throws \InvalidArgumentException
     */
    public function __construct(array $parameters)
    {
        if (!array_key_exists('value', $parameters)) {
            throw new \InvalidArgumentException("Type is required.");
        }

        $this->type = (string)$parameters['value'];
    }
}