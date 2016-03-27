<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;
use Apha\Annotations\ReadOnlyException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class EndSaga extends Annotation
{
    /**
     * @var string
     */
    private $methodName;

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