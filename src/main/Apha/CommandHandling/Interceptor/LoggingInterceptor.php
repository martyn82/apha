<?php
declare(strict_types = 1);

namespace Apha\CommandHandling\Interceptor;

use Apha\CommandHandling\CommandLogger;
use Apha\Message\Command;

class LoggingInterceptor implements CommandDispatchInterceptor
{
    /**
     * @var CommandLogger
     */
    private $logger;

    /**
     * @param CommandLogger $logger
     */
    public function __construct(CommandLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Command $command
     * @return void
     */
    public function onBeforeDispatch(Command $command)
    {
        $this->logger->onBeforeDispatch($command);
    }

    /**
     * @param Command $command
     * @return void
     */
    public function onDispatchSuccessful(Command $command)
    {
        $this->logger->onDispatchSuccessful($command);
    }

    /**
     * @param Command $command
     * @param \Exception $exception
     * @return void
     */
    public function onDispatchFailed(Command $command, \Exception $exception)
    {
        $this->logger->onDispatchFailed($command, $exception);
    }
}
