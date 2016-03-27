<?php
declare(strict_types = 1);

namespace Apha\Serializer;

/**
 * @group serializer
 */
class JsonSerializerTest extends SerializerTest
{
    /**
     * @test
     * @dataProvider serializableProvider
     * @param mixed $value
     * @param mixed $expected
     */
    public function serializeSerializesValue($value, $expected)
    {
        $serializer = new JsonSerializer();
        $serialized = $serializer->serialize($value);

        self::assertEquals($expected, $serialized);
    }

    /**
     * @test
     * @dataProvider deserializeProvider
     * @param mixed $serialized
     * @param string $type
     * @param mixed $expected
     */
    public function deserializeReconstructsValue($serialized, string $type, $expected)
    {
        $serializer = new JsonSerializer();
        $actual = $serializer->deserialize($serialized, $type);

        self::assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function serializableProvider(): array
    {
        return [
            [new Serializable(), '{"foo":"bar"}'],
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
    public function deserializeProvider(): array
    {
        return [
            ['{"foo":"bar"}', Serializable::class, new Serializable()],
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