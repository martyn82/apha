<?php
declare(strict_types = 1);

namespace Apha\CommandHandling\Interceptor;

use Apha\Message\Command;
use Apha\Serializer\ArrayConverter;
use Apha\Serializer\JsonSerializer;
use Apha\Serializer\Serializer;
use Psr\Log\LoggerInterface;

class LoggingCommandDispatchInterceptor implements CommandDispatchInterceptor
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ArrayConverter
     */
    private $converter;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->converter = new ArrayConverter();
    }

    /**
     * @param Command $command
     * @return void
     */
    public function onBeforeDispatch(Command $command)
    {
        $this->logger->info('Dispatch command', [
            'type' => get_class($command),
            'command' => $this->converter->objectToArray($command)
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
            'type' => get_class($command),
            'command' => $this->converter->objectToArray($command)
        ]);
    }
}