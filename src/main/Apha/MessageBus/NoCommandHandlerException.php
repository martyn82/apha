<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Command;

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