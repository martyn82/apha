<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Command;
use Apha\MessageHandler\CommandHandler;

abstract class CommandBus
{
    /**
     * @param string $commandClass
     * @param CommandHandler $handler
     * @throws CommandHandlerAlreadyExistsException
     * @return void
     */
    abstract public function addHandler(string $commandClass, CommandHandler $handler);

    /**
     * @param Command $command
     * @return void
     */
    abstract public function send(Command $command);
}