<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\ToDo;

use Apha\CommandHandling\CommandHandler;
use Apha\CommandHandling\TypedCommandHandler;
use Apha\Repository\Repository;

class ToDoCommandHandler implements CommandHandler
{
    use TypedCommandHandler;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param CreateToDoItem $command
     */
    public function handleCreateToDoItem(CreateToDoItem $command)
    {
        $item = ToDoItem::create($command);
        $this->repository->store($item);
    }

    /**
     * @param MarkItemDone $command
     */
    public function handleMarkItemDone(MarkItemDone $command)
    {
        /* @var $item ToDoItem */
        $item = $this->repository->findById($command->getIdentity());
        $item->markDone($command);
        $this->repository->store($item);
    }
}
