<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\User;

use Apha\CommandHandling\CommandHandler;
use Apha\Message\Command;
use Apha\Repository\Repository;
use Psr\Log\LoggerInterface;

class CreateUserHandler implements CommandHandler
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Repository $repository
     * @param LoggerInterface
     */
    public function __construct(Repository $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
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

        $user = User::create($command->getId());
        $this->repository->store($user);
    }
}