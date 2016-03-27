<?php
declare(strict_types = 1);

namespace Apha\EventHandling;

use Apha\Annotations\EventHandlerAnnotationReader;
use Apha\Message\Event;

trait AnnotatedEventHandler
{
    /**
     * @var array
     */
    private $annotatedEventHandlers = [];

    /**
     */
    private function readAnnotatedEventHandlers()
    {
        /* @var $this EventHandler|AnnotatedEventHandler */
        $reader = new EventHandlerAnnotationReader($this);

        /* @var $annotation \Apha\Annotations\Annotation\EventHandler */
        foreach ($reader->readAll() as $annotation) {
            if (empty($this->annotatedEventHandlers[$annotation->getEventType()])) {
                $this->annotatedEventHandlers[$annotation->getEventType()] = [];
            }

            $this->annotatedEventHandlers[$annotation->getEventType()][] = $annotation->getMethodName();
        }
    }

    /**
     * @param Event $event
     * @throws \InvalidArgumentException
     */
    public function on(Event $event)
    {
        if (empty($this->annotatedEventHandlers)) {
            $this->readAnnotatedEventHandlers();
        }

        $this->handleByAnnotatedEventHandler($event);
    }

    /**
     * @param Event $event
     * @throws \InvalidArgumentException
     */
    private function handleByAnnotatedEventHandler(Event $event)
    {
        $eventClass = get_class($event);

        if (!array_key_exists($eventClass, $this->annotatedEventHandlers)) {
            throw new \InvalidArgumentException("Unable to handle event '{$event->getName()}'.");
        }

        $handlerNames = $this->annotatedEventHandlers[$eventClass];

        foreach ($handlerNames as $handlerName) {
            $this->{$handlerName}($event);
        }
    }
}