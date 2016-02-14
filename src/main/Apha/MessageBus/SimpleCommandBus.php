<?php
declare(strict_types = 1);

namespace Apha\MessageBus;

use Apha\Message\Command;

class SimpleCommandBus extends CommandBus
{
    /**
     * @var array
     */
    private $commandHandlerMap;

    /**
     * @param array $commandHandlerMap
     */
    public function __construct(array $commandHandlerMap)
    {
        $this->commandHandlerMap = $commandHandlerMap;
    }

    /**
     * @param Command $command
     * @throws NoCommandHandlerException
     */
    public function send(Command $command)
    {
        $commandClassName = get_class($command);

        if (!array_key_exists($commandClassName, $this->commandHandlerMap)) {
            throw new NoCommandHandlerException($command);
        }

        $handler = $this->commandHandlerMap[$commandClassName];
        $handler->handle($command);
    }
}