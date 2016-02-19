<?php
declare(strict_types = 1);

namespace Apha\Serializer;

use JMS\Serializer\Annotation as Serializer;

abstract class SerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed $value
     * @param string $expected
     * @return void
     */
    abstract public function serializeSerializesValue($value, string $expected);

    /**
     * @param string $serialized
     * @param string $type
     * @param mixed $expected
     * @return void
     */
    abstract public function deserializeReconstructsValue(string $serialized, string $type, $expected);

    /**
     * @return array
     */
    public function serializableProvider() : array
    {
        return [
            [new SerializerTest_Serializable(), '{"foo":"bar"}'],
            [['foo' => 'bar'], '{"foo":"bar"}'],
            [[], "[]"],
            [
                ['int' => 123, 'bool-true' => true, 'bool-false' => false, 'str' => 'string', 'null' => null],
                '{"int":123,"bool-true":true,"bool-false":false,"str":"string"}' // null values are ignored
            ]
        ];
    }

    /**
     * @return array
     */
    public function deserializeProvider() : array
    {
        return [
            ['{"foo":"bar"}', SerializerTest_Serializable::class, new SerializerTest_Serializable()],
            ['{"foo":"bar"}', 'array', ['foo' => 'bar']],
            ["[]", 'array', []],
            [
                '{"int":123,"bool-true":true,"bool-false":false,"str":"string"}',
                'array',
                ['int' => 123, 'bool-true' => true, 'bool-false' => false, 'str' => 'string']
            ]
        ];
    }
}

class SerializerTest_Serializable
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $foo = 'bar';
}