<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Annotations\Annotation\AggregateIdentifier;
use Apha\Domain\Annotation\AnnotatedAggregateRoot;
use Apha\Domain\Identity;
use Apha\Examples\Domain\ToDo\CreateToDoItem;
use Apha\Examples\Domain\ToDo\DeadlineExpired;
use Apha\Examples\Domain\ToDo\MarkItemDone;
use Apha\Examples\Domain\ToDo\ToDoItemCreated;
use Apha\Examples\Domain\ToDo\ToDoItemDone;
use JMS\Serializer\Annotation as Serializer;

class ToDoItem extends AnnotatedAggregateRoot
{
    /**
     * @AggregateIdentifier(type="Apha\Domain\Identity")
     * @Serializer\Type("Apha\Domain\Identity")
     * @var Identity
     */
    protected $id;

    /**
     * @var bool
     */
    private $isDone = false;

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
     * @param ToDoItemCreated $event
     */
    public function applyToDoItemCreated(ToDoItemCreated $event)
    {
        $this->id = $event->getIdentity();
    }

    /**
     * @param ToDoItemDone $event
     */
    public function applyToDoItemDone(ToDoItemDone $event)
    {
        $this->isDone = true;
    }

    /**
     * @param DeadlineExpired $event
     */
    public function applyDeadlineExpired(DeadlineExpired $event)
    {
    }
}