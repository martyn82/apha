<?php
declare(strict_types = 1);

namespace Apha\Serializer;

use JMS\Serializer\Annotation as Serializer;

class Serializable
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $foo = 'bar';
}
