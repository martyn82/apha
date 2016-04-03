<?php
declare(strict_types = 1);

namespace Apha\CommandHandling;

use Apha\Message\Command;
use Apha\Serializer\ArrayConverter;
use Psr\Log\LoggerInterface;

class CommandLogger implements CommandDispatchHandler
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
        $this->logger->info('Command successfully dispatched', [
            'type' => get_class($command),
            'command' => $this->converter->objectToArray($command)
        ]);
    }

    /**
     * @param Command $command
     * @param \Exception $exception
     * @return void
     */
    public function onDispatchFailed(Command $command, \Exception $exception)
    {
        $message = 'Dispatch failed!';

        if ($exception instanceOf NoCommandHandlerException) {
            $message = 'Dead letter message encountered!';
        }

        $this->logger->error($message, [
            'type' => get_class($command),
            'command' => $this->converter->objectToArray($command),
            'exception' => $exception->getMessage()
        ]);
    }
}
