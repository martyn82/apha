<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Message\Message;

class UnresolvableParameterException extends \Exception
{
    /**
     * @var string
     */
    private static $messageTemplate = "Unable to resolve parameter '%s' from message '%s'.";

    /**
     * @param Message $message
     * @param string $propertyName
     */
    public function __construct(Message $message, string $propertyName)
    {
        parent::__construct(sprintf(static::$messageTemplate, $propertyName, get_class($message)));
    }
}