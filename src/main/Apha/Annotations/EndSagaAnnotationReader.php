<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\Annotation;
use Apha\Annotations\Annotation\EndSaga;
use Apha\Saga\Annotation\AnnotatedSaga;

class EndSagaAnnotationReader extends AnnotationReader
{
    /**
     * @param AnnotatedSaga $saga
     */
    public function __construct(AnnotatedSaga $saga)
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        parent::__construct(get_class($saga), $reader, [AnnotationType::ANNOTATED_METHOD]);
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
        return array_reduce(
            $annotations,
            function (array $accumulator, Annotation $annotation) use ($reflectionMethod) {
                if (!($annotation instanceof EndSaga)) {
                    return $accumulator;
                }

                $accumulator[] = $this->processAnnotation($annotation, $reflectionMethod);
                return $accumulator;
            },
            []
        );
    }

    /**
     * @param \Apha\Annotations\Annotation\EndSaga $annotation
     * @param \ReflectionMethod $reflectionMethod
     * @return \Apha\Annotations\Annotation\EndSaga
     * @throws AnnotationReaderException
     */
    private function processAnnotation(
        \Apha\Annotations\Annotation\EndSaga $annotation,
        \ReflectionMethod $reflectionMethod
    ): \Apha\Annotations\Annotation\EndSaga
    {
        $annotation->setMethodName($reflectionMethod->getName());
        return $annotation;
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
}