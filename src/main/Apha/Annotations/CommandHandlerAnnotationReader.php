<?php
declare(strict_types = 1);

namespace Apha\Annotations;

use Apha\Annotations\Annotation\Annotation;
use Apha\CommandHandling\CommandHandler;

class CommandHandlerAnnotationReader extends AnnotationReader
{
    /**
     * @param CommandHandler $handler
     */
    public function __construct(CommandHandler $handler)
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
                if (!($annotation instanceof \Apha\Annotations\Annotation\CommandHandler)) {
                    return $accumulator;
                }

                $accumulator[] = $this->processAnnotation($annotation, $reflectionMethod);
                return $accumulator;
            },
            []
        );
    }

    /**
     * @param \Apha\Annotations\Annotation\CommandHandler $annotation
     * @param \ReflectionMethod $reflectionMethod
     * @return \Apha\Annotations\Annotation\CommandHandler
     * @throws AnnotationReaderException
     */
    private function processAnnotation(
        \Apha\Annotations\Annotation\CommandHandler $annotation,
        \ReflectionMethod $reflectionMethod
    ): \Apha\Annotations\Annotation\CommandHandler
    {
        $parameters = $reflectionMethod->getParameters();

        if (empty($parameters)) {
            throw new AnnotationReaderException("CommandHandler needs a parameter that accepts a Command.");
        }

        $annotation->methodName = $reflectionMethod->getName();
        $annotation->commandType = $parameters[0]->getType()->__toString();

        return $annotation;
    }
}
