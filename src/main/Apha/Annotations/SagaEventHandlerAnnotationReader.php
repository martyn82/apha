<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\Annotation;
use Apha\EventHandling\EventHandler;

class SagaEventHandlerAnnotationReader extends AnnotationReader
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
     * @throws AnnotationReaderException
     */
    protected function processMethodAnnotations(array $annotations, \ReflectionMethod $reflectionMethod): array
    {
        return array_reduce(
            $annotations,
            function (array $accumulator, Annotation $annotation) use ($reflectionMethod) {
                if (!($annotation instanceof \Apha\Annotations\Annotation\SagaEventHandler)) {
                    return $accumulator;
                }

                $accumulator[] = $this->processAnnotation($annotation, $reflectionMethod);
                return $accumulator;
            },
            []
        );
    }

    /**
     * @param \Doctrine\Common\Annotations\Annotation[] $annotations
     * @param \ReflectionProperty $reflectionProperty
     * @return Annotation[]
     * @throws AnnotationReaderException
     */
    protected function processPropertyAnnotations(
        array $annotations,
        \ReflectionProperty $reflectionProperty
    ): array
    {
        return [];
    }

    /**
     * @param \Apha\Annotations\Annotation\SagaEventHandler $annotation
     * @param \ReflectionMethod $reflectionMethod
     * @return \Apha\Annotations\Annotation\SagaEventHandler
     * @throws AnnotationReaderException
     */
    private function processAnnotation(
        \Apha\Annotations\Annotation\SagaEventHandler $annotation,
        \ReflectionMethod $reflectionMethod
    ): \Apha\Annotations\Annotation\SagaEventHandler
    {
        $parameters = $reflectionMethod->getParameters();

        if (empty($parameters)) {
            throw new AnnotationReaderException("SagaEventHandler needs a parameter that accepts an Event.");
        }

        $annotation->setMethodName($reflectionMethod->getName());
        $annotation->setEventType($parameters[0]->getType()->__toString());

        return $annotation;
    }
}
