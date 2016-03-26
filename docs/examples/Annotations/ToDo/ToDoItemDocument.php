<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Examples\Domain\ToDo\DeadlineExpired;
use Apha\Examples\Domain\ToDo\ToDoItemCreated;
use Apha\Examples\Domain\ToDo\ToDoItemDone;
use Apha\StateStore\Document;
use Apha\StateStore\TypedEventApplicator;

class ToDoItemDocument implements Document
{
    use TypedEventApplicator;

    /**
     * @var string
     */
    private $identity;

    /**
     * @var int
     */
    private $version;

    /**
     * @var bool
     */
    private $isDone;

    /**
     * @var bool
     */
    private $isExpired;

    /**
     * @param string $identity
     * @param int $version
     */
    public function __construct(string $identity, int $version)
    {
        $this->identity = $identity;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->identity;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param ToDoItemCreated $event
     */
    public function applyToDoItemCreated(ToDoItemCreated $event)
    {
        $this->identity = $event->getIdentity();
        $this->isDone = false;
        $this->isExpired = false;
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
        $this->isExpired = true;
    }
}
