<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

use Apha\Annotations\ReadOnlyException;
use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *     @Attribute("type", type = "string", required = true)
 * })
 */
final class AggregateIdentifier extends Annotation
{
    /**
     * @var string
     */
    private $propertyName;

    /**
     * @var string
     */
    private $type;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->type = $attributes['type'];
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * @param string $propertyName
     * @throws ReadOnlyException
     */
    public function setPropertyName(string $propertyName)
    {
        if (!empty($this->propertyName)) {
            throw new ReadOnlyException('propertyName');
        }

        $this->propertyName = $propertyName;
    }
}