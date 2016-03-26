<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\ToDo;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;

class ToDoItem extends AggregateRoot
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var bool
     */
    private $isDone;

    /**
     * @param CreateToDoItem $command
     * @return ToDoItem
     */
    public static function create(CreateToDoItem $command): self
    {
        $instance = new self();
        $instance->applyChange(
            new ToDoItemCreated($command->getIdentity(), $command->getDescription(), $command->getExpireSeconds())
        );
        return $instance;
    }

    /**
     */
    protected function __construct()
    {
        parent::__construct();
        $this->isDone = false;
    }

    /**
     * @param MarkItemDone $command
     */
    public function markDone(MarkItemDone $command)
    {
        $this->applyChange(new ToDoItemDone($command->getIdentity()));
    }

    /**
     * @return Identity
     */
    public function getId(): Identity
    {
        return $this->identity;
    }

    /**
     * @param ToDoItemCreated $event
     */
    public function applyToDoItemCreated(ToDoItemCreated $event)
    {
        $this->identity = $event->getIdentity();
    }

    /**
     * @param ToDoItemDone $event
     */
    public function applyToDoItemDone(ToDoItemDone $event)
    {
        $this->isDone = true;
    }
}
