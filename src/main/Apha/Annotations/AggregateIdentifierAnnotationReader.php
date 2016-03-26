<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\Annotation;
use Apha\Domain\Annotation\AnnotatedAggregateRoot;

class AggregateIdentifierAnnotationReader extends AnnotationReader
{
    /**
     * @var array
     */
    private $validScalarTypes = [
        'integer',
        'string'
    ];

    /**
     * @param AnnotatedAggregateRoot $aggregateRoot
     */
    public function __construct(AnnotatedAggregateRoot $aggregateRoot)
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        parent::__construct(get_class($aggregateRoot), $reader, [AnnotationType::ANNOTATED_PROPERTY]);
    }

    /**
     * @param \Doctrine\Common\Annotations\Annotation[] $annotations
     * @param \ReflectionMethod $reflectionMethod
     * @return Annotation[]
     * @throws AnnotationReaderException
     */
    protected function processMethodAnnotations(
        array $annotations,
        \ReflectionMethod $reflectionMethod
    ): array
    {
        return [];
    }

    /**
     * @param \Doctrine\Common\Annotations\Annotation[] $annotations
     * @param \ReflectionProperty $reflectionProperty
     * @return Annotation[]
     * @throws AnnotationReaderException
     */
    protected function processPropertyAnnotations(array $annotations, \ReflectionProperty $reflectionProperty): array
    {
        return array_reduce(
            $annotations,
            function (array $accumulator, Annotation $annotation) use ($reflectionProperty) {
                if (!($annotation instanceof \Apha\Annotations\Annotation\AggregateIdentifier)) {
                    return $accumulator;
                }

                $accumulator[] = $this->processAnnotation($annotation, $reflectionProperty);
                return $accumulator;
            },
            []
        );
    }

    /**
     * @param \Apha\Annotations\Annotation\AggregateIdentifier $annotation,
     * @param \ReflectionProperty $reflectionProperty
     * @return \Apha\Annotations\Annotation\AggregateIdentifier
     * @throws AnnotationReaderException
     */
    private function processAnnotation(
        \Apha\Annotations\Annotation\AggregateIdentifier $annotation,
        \ReflectionProperty $reflectionProperty
    ): \Apha\Annotations\Annotation\AggregateIdentifier
    {
        if (
            !in_array($annotation->type, $this->validScalarTypes)
            && !class_exists($annotation->type, true)
        ) {
            throw new AnnotationReaderException("Type '{$annotation->type}' is not a valid AggregateIdentifier type.");
        }

        if ($reflectionProperty->isPrivate()) {
            throw new AnnotationReaderException("Property must not be private.");
        }

        $annotation->propertyName = $reflectionProperty->getName();
        return $annotation;
    }
}