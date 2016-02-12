<?php

namespace Apha\MessageHandler;

use Apha\Domain\Message\Command;

trait TypedCommandHandler
{
    /**
     * @param Command $command
     * @throws \InvalidArgumentException
     */
    public function handle(Command $command)
    {
        $this->handleByInflection($command);
    }

    /**
     * @param Command $command
     * @throws \InvalidArgumentException
     */
    private function handleByInflection(Command $command)
    {
        $commandClassName = get_class($command);
        $classNameParts = explode('\\', $commandClassName);
        $commandName = end($classNameParts);
        $commandHandleMethod = 'handle' . $commandName;

        if (!method_exists($this, $commandHandleMethod)) {
            throw new \InvalidArgumentException("Unable to handle command '{$commandName}'.");
        }

        $this->{$commandHandleMethod}($command);
    }
}