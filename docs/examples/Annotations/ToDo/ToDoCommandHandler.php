<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Annotations\Annotation as Apha;
use Apha\CommandHandling\AnnotatedCommandHandler;
use Apha\CommandHandling\CommandHandler;
use Apha\Examples\Domain\ToDo\CreateToDoItem;
use Apha\Examples\Domain\ToDo\MarkItemDone;
use Apha\Examples\Domain\ToDo\ToDoItem;
use Apha\Repository\Repository;

class ToDoCommandHandler implements CommandHandler
{
    use AnnotatedCommandHandler;

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
     * @Apha\CommandHandler()
     * @param CreateToDoItem $command
     */
    public function handleCreateToDoItem(CreateToDoItem $command)
    {
        $item = ToDoItem::create($command);
        $this->repository->store($item);
    }

    /**
     * @Apha\CommandHandler()
     * @param MarkItemDone $command
     */
    public function handleMarkItemDone(MarkItemDone $command)
    {
        /* @var $item ToDoItem */
        $item = $this->repository->findById($command->getIdentity());
        $item->markDone($command);
        $this->repository->store($item, $item->getVersion());
    }
}