<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 *     @Attribute("associationProperty", type = "string", required = true)
 * })
 */
final class SagaEventHandler extends Annotation
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @var string
     */
    private $associationProperty;

    /**
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->associationProperty = $attributes['associationProperty'];
    }

    /**
     * @return string
     */
    public function getAssociationProperty(): string
    {
        return $this->associationProperty;
    }

    /**
     * @return string
     */
    public function getMethodName(): String
    {
        return $this->methodName;
    }

    /**
     * @param string $methodName
     */
    public function setMethodName(string $methodName)
    {
        $this->methodName = $methodName;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @param string $eventType
     */
    public function setEventType(string $eventType)
    {
        $this->eventType = $eventType;
    }
}
