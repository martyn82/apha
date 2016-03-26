<?php
declare(strict_types = 1);

namespace Apha\Examples\Annotations\ToDo;

use Apha\Annotations\Annotation as Apha;
use Apha\EventHandling\AnnotatedEventHandler;
use Apha\EventHandling\EventHandler;
use Apha\Examples\Domain\ToDo\DeadlineExpired;
use Apha\Examples\Domain\ToDo\ToDoItemCreated;
use Apha\Examples\Domain\ToDo\ToDoItemDone;
use Apha\StateStore\Storage\StateStorage;

class ToDoItemProjections implements EventHandler
{
    use AnnotatedEventHandler;

    /**
     * @var StateStorage
     */
    private $stateStorage;

    /**
     * @param StateStorage $stateStorage
     */
    public function __construct(StateStorage $stateStorage)
    {
        $this->stateStorage = $stateStorage;
    }

    /**
     * @Apha\EventHandler()
     * @param ToDoItemCreated $event
     */
    public function onToDoItemCreated(ToDoItemCreated $event)
    {
        $document = new ToDoItemDocument($event->getIdentity()->getValue(), $event->getVersion());
        $this->stateStorage->upsert($event->getIdentity()->getValue(), $document);
    }

    /**
     * @Apha\EventHandler()
     * @param ToDoItemDone $event
     */
    public function onToDoItemDone(ToDoItemDone $event)
    {
        $document = $this->stateStorage->find($event->getIdentity()->getValue());
        $document->apply($event);
        $this->stateStorage->upsert($event->getIdentity()->getValue(), $document);
    }

    /**
     * @Apha\EventHandler()
     * @param DeadlineExpired $event
     */
    public function onDeadlineExpired(DeadlineExpired $event)
    {
        $this->stateStorage->delete($event->getIdentity()->getValue());
    }
}