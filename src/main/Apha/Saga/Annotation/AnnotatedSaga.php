<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Annotations\Annotation\SagaEventHandler;
use Apha\Annotations\SagaEventHandlerAnnotationReader;
use Apha\Domain\Identity;
use Apha\EventHandling\EventHandler;
use Apha\Message\Event;
use Apha\Saga\AssociationValue;
use Apha\Saga\AssociationValues;
use Apha\Saga\Saga;
use JMS\Serializer\Annotation as Serializer;

abstract class AnnotatedSaga extends Saga
{
    /**
     * @var bool
     */
    private $isActive;

    /**
     * @Serializer\Exclude()
     * @var array
     */
    private $annotatedEventHandlers = [];

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        parent::__construct($identity, new AssociationValues([]));
    }

    /**
     * @param Event $event
     * @return void
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

        $handlers = $this->annotatedEventHandlers[$eventClass];

        /* @var $handler SagaEventHandler */
        foreach ($handlers as $handler) {
            $associationValue = (string)call_user_func([$event, 'get' . ucfirst($handler->getAssociationProperty())]);
            $this->associateWith(new AssociationValue($handler->getAssociationProperty(), $associationValue));

            $handlerName = $handler->getMethodName();
            $this->{$handlerName}($event);
        }
    }

    /**
     */
    private function readAnnotatedEventHandlers()
    {
        /* @var $this EventHandler|AnnotatedSaga */
        $reader = new SagaEventHandlerAnnotationReader($this);

        /* @var $annotation \Apha\Annotations\Annotation\EventHandler */
        foreach ($reader->readAll() as $annotation) {
            if (empty($this->annotatedEventHandlers[$annotation->getEventType()])) {
                $this->annotatedEventHandlers[$annotation->getEventType()] = [];
            }

            $this->annotatedEventHandlers[$annotation->getEventType()][] = $annotation;
        }
    }

    /**
     * @param AssociationValue $associationValue
     */
    public function associateWith(AssociationValue $associationValue)
    {
        $this->associationValues->add($associationValue);
    }

    /**
     * @return bool
     */
    final public function isActive(): bool
    {
        return true;
    }

    /**
     */
    public function start()
    {
        $this->isActive = true;
    }

    /**
     */
    public function end()
    {
        $this->isActive = false;
    }
}