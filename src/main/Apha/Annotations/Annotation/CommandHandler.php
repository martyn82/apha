<?php
declare(strict_types = 1);

namespace Apha\Annotations\Annotation;
use Apha\Annotations\ReadOnlyException;

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
    public function getCommandType(): string
    {
        return $this->commandType;
    }

    /**
     * @param string $commandType
     * @throws ReadOnlyException
     */
    public function setCommandType(string $commandType)
    {
        if (!empty($this->commandType)) {
            throw new ReadOnlyException('commandType');
        }

        $this->commandType = $commandType;
    }
}
