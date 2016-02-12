<?php

namespace Apha\MessageBus;

use Apha\Domain\Message\Command;

class NoCommandHandlerException extends \Exception
{
    /**
     * @var string
     */
    private static $messageTemplate = "No handler for command '%s'.";

    /**
     * @param Command $command
     */
    public function __construct(Command $command)
    {
        parent::__construct(sprintf(static::$messageTemplate, get_class($command)));
    }
}