<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\Demonstrate;

use Apha\CommandHandling\CommandHandler;
use Apha\Message\Command;
use Psr\Log\LoggerInterface;

class DemonstrateHandler implements CommandHandler
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
     */
    public function handle(Command $command)
    {
        $commandName = get_class($command);
        $this->logger->info('Handle command', [
            'command' => $commandName,
            'handler' => get_class($this)
        ]);
    }
}