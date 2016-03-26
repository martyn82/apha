<?php
declare(strict_types = 1);

namespace Apha\CommandHandling;

use Apha\Annotations\CommandHandlerAnnotationReader;
use Apha\Message\Command;

trait AnnotatedCommandHandler
{
    /**
     * @var array
     */
    private $annotatedCommandHandlers = [];

    /**
     * @throws CommandHandlerAlreadyExistsException
     */
    private function readAnnotatedCommandHandlers()
    {
        /* @var $this CommandHandler|AnnotatedCommandHandler */
        $reader = new CommandHandlerAnnotationReader($this);

        /* @var $annotation \Apha\Annotations\Annotation\CommandHandler */
        foreach ($reader->readAll() as $annotation) {
            if (!empty($this->annotatedCommandHandlers[$annotation->commandType])) {
                throw new CommandHandlerAlreadyExistsException($annotation->commandType);
            }

            $this->annotatedCommandHandlers[$annotation->commandType] = $annotation->methodName;
        }
    }

    /**
     * @param Command $command
     * @throws \InvalidArgumentException
     */
    public function handle(Command $command)
    {
        if (empty($this->annotatedCommandHandlers)) {
            $this->readAnnotatedCommandHandlers();
        }

        $this->handleByAnnotatedCommandHandler($command);
    }

    /**
     * @param Command $command
     * @throws \InvalidArgumentException
     */
    private function handleByAnnotatedCommandHandler(Command $command)
    {
        $commandClass = get_class($command);

        if (!array_key_exists($commandClass, $this->annotatedCommandHandlers)) {
            $classNameParts = explode("\\", $commandClass);
            $commandName = end($classNameParts);
            throw new \InvalidArgumentException("Unable to handle command '{$commandName}'.");
        }

        $handlerName = $this->annotatedCommandHandlers[$commandClass];
        $this->{$handlerName}($command);
    }
}
