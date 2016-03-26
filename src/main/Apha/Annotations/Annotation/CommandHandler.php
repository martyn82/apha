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
    public $methodName;

    /**
     * @var string
     */
    public $commandType;
}
