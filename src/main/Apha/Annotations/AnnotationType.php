<?php
declare(strict_types = 1);

namespace Apha\Annotations;

class AnnotationType
{
    /**
     * @var int
     */
    const ANNOTATED_METHOD = 1;

    /**
     * @var int
     */
    const ANNOTATED_PROPERTY = 2;

    /**
     * @var int
     */
    const ANNOTATED_CLASS = 3;
}
