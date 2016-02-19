<?php
declare(strict_types = 1);

namespace Apha\Serializer;

class JsonSerializerTest extends SerializerTest
{
    /**
     * @test
     * @dataProvider serializableProvider
     * @param mixed $value
     * @param string $expected
     */
    public function serializeSerializesValue($value, string $expected)
    {
        $serializer = new JsonSerializer();
        $serialized = $serializer->serialize($value);

        self::assertEquals($expected, $serialized);
    }

    /**
     * @test
     * @dataProvider deserializeProvider
     * @param string $serialized
     * @param string $type
     * @param mixed $expected
     */
    public function deserializeReconstructsValue(string $serialized, string $type, $expected)
    {
        $serializer = new JsonSerializer();
        $actual = $serializer->deserialize($serialized, $type);

        self::assertEquals($expected, $actual);
    }
}