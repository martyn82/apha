<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class CommandHandler extends Annotation
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var string
     */
    private $commandType;

    /**
     * @return string
     */
    public function getMethodName(): string
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
    public function getCommandType(): string
    {
        return $this->commandType;
    }

    /**
     * @param string $commandType
     */
    public function setCommandType(string $commandType)
    {
        $this->commandType = $commandType;
    }
}
