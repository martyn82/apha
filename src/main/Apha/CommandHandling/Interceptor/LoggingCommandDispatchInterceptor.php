<?php
declare(strict_types = 1);

namespace Apha\CommandHandling\Interceptor;

use Apha\Message\Command;
use Psr\Log\LoggerInterface;

class LoggingCommandDispatchInterceptor implements CommandDispatchInterceptor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Command $command
     * @return void
     */
    public function onBeforeDispatch(Command $command)
    {
        $this->logger->info('Dispatch command', [
            'command' => get_class($command)
        ]);
    }

    /**
     * @param Command $command
     * @return void
     */
    public function onDispatchSuccessful(Command $command)
    {
    }

    /**
     * @param Command $command
     * @param \Exception $exception
     * @return void
     */
    public function onDispatchFailed(Command $command, \Exception $exception)
    {
        $this->logger->error('Dead letter message', [
            'command' => get_class($command)
        ]);
    }
}