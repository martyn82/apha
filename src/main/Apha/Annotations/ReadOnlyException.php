<?php
declare(strict_types = 1);

namespace Apha\Annotations;

class ReadOnlyException extends \Exception
{
    /**
     * @var string
     */
    private static $messageTemplate = "The property '%s' can only be written once.";

    /**
     * @param string $propertyName
     */
    public function __construct(string $propertyName)
    {
        parent::__construct(sprintf(static::$messageTemplate, $propertyName));
    }
}