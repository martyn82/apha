<?php
declare(strict_types = 1);

namespace Apha\CommandHandling;

class CommandHandlerAlreadyExistsException extends \Exception
{
    /**
     * @var string
     */
    private static $messageTemplate = "Only one command handler can be registered for command '%s'.";

    /**
     * @param string $commandClass
     */
    public function __construct(string $commandClass)
    {
        parent::__construct(sprintf(static::$messageTemplate, $commandClass));
    }
}