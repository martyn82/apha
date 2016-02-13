<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Command;

abstract class CommandBus
{
    /**
     * @param Command $command
     * @return void
     */
    abstract public function send(Command $command);
}