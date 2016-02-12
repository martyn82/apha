<?php

namespace Apha\MessageBus;

use Apha\Domain\Message\Command;

class CommandBus
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

        if (!isset($this->commandHandlerMap[$commandClassName])) {
            throw new NoCommandHandlerException($command);
        }

        $handler = $this->commandHandlerMap[$commandClassName];
        $handler->handle($command);
    }
}