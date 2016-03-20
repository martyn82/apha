<?php
declare(strict_types = 1);

namespace Apha\Serializer;

use JMS\Serializer\Annotation as Serializer;

abstract class SerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $value
     * @param mixed $expected
     * @return void
     */
    abstract public function serializeSerializesValue($value, $expected);

    /**
     * @param mixed $serialized
     * @param string $type
     * @param mixed $expected
     * @return void
     */
    abstract public function deserializeReconstructsValue($serialized, string $type, $expected);
}
