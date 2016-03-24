<?php
declare(strict_types = 1);

namespace Apha\Saga;

class SagaCreationPolicy
{
    /**
     * @var int
     */
    const NEVER = 0;

    /**
     * @var int
     */
    const IF_NONE_FOUND = 1;

    /**
     * @var int
     */
    const ALWAYS = 2;
}
