<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\Annotation;
use Apha\EventHandling\EventHandler;

class EventHandlerAnnotationReader extends AnnotationReader
{
    /**
     * @param EventHandler $handler
     */
    public function __construct(EventHandler $handler)
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        parent::__construct(get_class($handler), $reader, [AnnotationType::ANNOTATED_METHOD]);
    }

    /**
     * @param \Doctrine\Common\Annotations\Annotation[] $annotations
     * @param \ReflectionMethod $reflectionMethod
     * @return Annotation[]
     */
    protected function processAnnotations(array $annotations, \ReflectionMethod $reflectionMethod): array
    {
        return array_reduce(
            $annotations,
            function (array $accumulator, Annotation $annotation) use ($reflectionMethod) {
                if (!($annotation instanceof \Apha\Annotations\Annotation\EventHandler)) {
                    return $accumulator;
                }

                $accumulator[] = $this->processAnnotation($annotation, $reflectionMethod);
                return $accumulator;
            },
            []
        );
    }

    /**
     * @param \Apha\Annotations\Annotation\EventHandler $annotation
     * @param \ReflectionMethod $reflectionMethod
     * @return \Apha\Annotations\Annotation\EventHandler
     * @throws AnnotationReaderException
     */
    private function processAnnotation(
        \Apha\Annotations\Annotation\EventHandler $annotation,
        \ReflectionMethod $reflectionMethod
    ): \Apha\Annotations\Annotation\EventHandler
    {
        $parameters = $reflectionMethod->getParameters();

        if (empty($parameters)) {
            throw new AnnotationReaderException("EventHandler needs a parameter that accepts an Event.");
        }

        $annotation->methodName = $reflectionMethod->getName();
        $annotation->eventType = $parameters[0]->getType()->__toString();

        return $annotation;
    }
}
