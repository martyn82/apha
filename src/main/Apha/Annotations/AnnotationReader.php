<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\Annotation;
use Doctrine\Common\Annotations\Reader;

abstract class AnnotationReader
{
    /**
     * @var string
     */
    private $handlerClass;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var array
     */
    private $annotationTypes;

    /**
     * @param string $handlerClass
     * @param Reader $reader
     * @param array $annotationTypes
     */
    public function __construct(string $handlerClass, Reader $reader, array $annotationTypes)
    {
        $this->handlerClass = $handlerClass;
        $this->reader = $reader;
        $this->annotationTypes = $annotationTypes;
    }

    /**
     * @return Annotation[]
     * @throws AnnotationReaderException
     */
    public function readAll(): array
    {
        $annotations = [];
        $reflectedClass = new \ReflectionClass($this->handlerClass);

        if (in_array(AnnotationType::ANNOTATED_METHOD, $this->annotationTypes)) {
            $annotations = array_merge($annotations, $this->readMethodAnnotations($reflectedClass));
        }

        return $annotations;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return Annotation[]
     * @throws AnnotationReaderException
     */
    private function readMethodAnnotations(\ReflectionClass $reflectionClass): array
    {
        return array_reduce(
            $reflectionClass->getMethods(),
            function (array $accumulator, \ReflectionMethod $reflectionMethod) {
                /* @var $annotations \Doctrine\Common\Annotations\Annotation[] */
                $annotations = $this->reader->getMethodAnnotations($reflectionMethod);

                if (empty($annotations)) {
                    return $accumulator;
                }

                $accumulator = array_merge($accumulator, $this->processAnnotations($annotations, $reflectionMethod));
                return $accumulator;
            },
            []
        );
    }

    /**
     * @param \Doctrine\Common\Annotations\Annotation[] $annotations
     * @param \ReflectionMethod $reflectionMethod
     * @return Annotation[]
     */
    abstract protected function processAnnotations(array $annotations, \ReflectionMethod $reflectionMethod): array;
}
