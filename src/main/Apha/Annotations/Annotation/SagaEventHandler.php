<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

use Apha\Annotations\ReadOnlyException;
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
     * @throws ReadOnlyException
     */
    public function setMethodName(string $methodName)
    {
        if (!empty($this->methodName)) {
            throw new ReadOnlyException('methodName');
        }

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
     * @throws ReadOnlyException
     */
    public function setEventType(string $eventType)
    {
        if (!empty($this->eventType)) {
            throw new ReadOnlyException('eventType');
        }

        $this->eventType = $eventType;
    }
}
