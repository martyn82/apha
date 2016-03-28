<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Annotations\Annotation\EndSaga;
use Apha\Annotations\Annotation\SagaEventHandler;
use Apha\Annotations\Annotation\StartSaga;
use Apha\Annotations\DefaultParameterResolver;
use Apha\Annotations\EndSagaAnnotationReader;
use Apha\Annotations\ParameterResolver;
use Apha\Annotations\SagaEventHandlerAnnotationReader;
use Apha\Annotations\StartSagaAnnotationReader;
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
     * @Serializer\Type("boolean")
     * @var bool
     */
    private $isActive = false;

    /**
     * @Serializer\Exclude()
     * @var ParameterResolver
     */
    private $parameterResolver;

    /**
     * @Serializer\Exclude()
     * @var array
     */
    private $annotatedEventHandlers = [];

    /**
     * @Serializer\Exclude()
     * @var array
     */
    private $annotatedSagaStarters = [];

    /**
     * @Serializer\Exclude()
     * @var array
     */
    private $annotatedSagaEndings = [];

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity = null)
    {
        if ($identity == null) {
            $identity = Identity::createNew();
        }

        $this->setParameterResolver(new DefaultParameterResolver());
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

        if (empty($this->annotatedSagaStarters)) {
            $this->readAnnotatedStarters();
        }

        if (empty($this->annotatedSagaEndings)) {
            $this->readAnnotatedEndings();
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
            $associatedValue = (string)$this->parameterResolver->resolveParameterValue(
                $event,
                $handler->getAssociationProperty()
            );
            $this->associateWith(new AssociationValue($handler->getAssociationProperty(), $associatedValue));

            $handlerName = $handler->getMethodName();

            if (in_array($handlerName, $this->annotatedSagaStarters)) {
                $this->start();
            }

            $this->{$handlerName}($event);

            if (in_array($handlerName, $this->annotatedSagaEndings)) {
                $this->end();
            }
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
     * @throws NoStartSagaHandlerException
     */
    private function readAnnotatedStarters()
    {
        /* @var $this EventHandler|AnnotatedSaga */
        $reader = new StartSagaAnnotationReader($this);
        $annotations = $reader->readAll();

        if (empty($annotations)) {
            throw new NoStartSagaHandlerException($this);
        }

        /* @var $annotation StartSaga */
        foreach ($annotations as $annotation) {
            $this->annotatedSagaStarters[] = $annotation->getMethodName();
        }
    }

    /**
     * @throws NoEndSagaHandlerException
     */
    private function readAnnotatedEndings()
    {
        $reader = new EndSagaAnnotationReader($this);
        $annotations = $reader->readAll();

        if (empty($annotations)) {
            throw new NoEndSagaHandlerException($this);
        }

        /* @var $annotation EndSaga */
        foreach ($annotations as $annotation) {
            $this->annotatedSagaEndings[] = $annotation->getMethodName();
        }
    }

    /**
     * @param ParameterResolver $parameterResolver
     */
    public function setParameterResolver(ParameterResolver $parameterResolver)
    {
        $this->parameterResolver = $parameterResolver;
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
        return $this->isActive;
    }

    /**
     */
    protected function start()
    {
        $this->isActive = true;
    }

    /**
     */
    protected function end()
    {
        $this->isActive = false;
    }
}