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
     * @return void
     * @throws CommandHandlerAlreadyExistsException
     */
    abstract public function addHandler(string $commandClass, CommandHandler $handler);

    /**
     * @param Command $command
     * @return void
     * @throws NoCommandHandlerException
     */
    abstract public function send(Command $command);
}