<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;
use Apha\Annotations\ReadOnlyException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class EventHandler extends Annotation
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

    /**
     * @return string
     */
    public function getMethodName(): string
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
}
