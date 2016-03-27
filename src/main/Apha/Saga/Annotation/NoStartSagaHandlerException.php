<?php
declare(strict_types = 1);

namespace Apha\Saga\Annotation;

use Apha\Annotations\AnnotationReaderException;

class NoStartSagaHandlerException extends AnnotationReaderException
{
    /**
     * @var string
     */
    private static $messageTemplate = "Saga '%s' has no StartSaga handler.";

    /**
     * @param AnnotatedSaga $saga
     */
    public function __construct(AnnotatedSaga $saga)
    {
        parent::__construct(sprintf(static::$messageTemplate, get_class($saga)));
    }
}