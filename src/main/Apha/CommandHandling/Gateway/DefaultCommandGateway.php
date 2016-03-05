<?php
declare(strict_types = 1);

namespace Apha\CommandHandling\Gateway;

use Apha\CommandHandling\CommandBus;
use Apha\CommandHandling\Interceptor\CommandDispatchInterceptor;
use Apha\CommandHandling\NoCommandHandlerException;
use Apha\Message\Command;

class DefaultCommandGateway implements CommandGateway
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var CommandDispatchInterceptor[]
     */
    private $interceptors;

    /**
     * @param CommandBus $commandBus
     * @param CommandDispatchInterceptor[] $commandInterceptors
     */
    public function __construct(CommandBus $commandBus, array $commandInterceptors = [])
    {
        $this->commandBus = $commandBus;

        $this->interceptors = array_map(
            function (CommandDispatchInterceptor $interceptor): CommandDispatchInterceptor {
                return $interceptor;
            },
            $commandInterceptors
        );
    }

    /**
     * @param Command $command
     */
    public function send(Command $command)
    {
        $this->notifyBeforeDispatch($command);

        try {
            $this->commandBus->send($command);
            $this->notifyDispatchSuccessful($command);
        } catch (NoCommandHandlerException $e) {
            $this->notifyDispatchFailed($command, $e);
        }
    }

    /**
     * @param Command $command
     */
    private function notifyBeforeDispatch(Command $command)
    {
        foreach ($this->interceptors as $interceptor) {
            /* @var $interceptor CommandDispatchInterceptor */
            $interceptor->onBeforeDispatch($command);
        }
    }

    /**
     * @param Command $command
     */
    private function notifyDispatchSuccessful(Command $command)
    {
        foreach ($this->interceptors as $interceptor) {
            /* @var $interceptor CommandDispatchInterceptor */
            $interceptor->onDispatchSuccessful($command);
        }
    }

    /**
     * @param Command $command
     * @param \Exception $exception
     */
    private function notifyDispatchFailed(Command $command, \Exception $exception)
    {
        foreach ($this->interceptors as $interceptor) {
            /* @var $interceptor CommandDispatchInterceptor */
            $interceptor->onDispatchFailed($command, $exception);
        }
    }
}