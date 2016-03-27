<?php
declare(strict_types = 1);

namespace Apha\Serializer;

/**
 * @group serializer
 */
class ArrayConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider serializableProvider
     * @param mixed $value
     * @param mixed $expected
     */
    public function objectToArrayConvertsAnythingToArray($value, $expected)
    {
        $converter = new ArrayConverter();

        $actual = $converter->objectToArray($value);
        self::assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider deserializeProvider
     * @param array $serialized
     * @param string $type
     * @param mixed $expected
     */
    public function deserializeReconstructsValue(array $serialized, string $type = null, $expected)
    {
        $converter = new ArrayConverter();

        $actual = $converter->arrayToObject($serialized, $type);
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function serializableProvider(): array
    {
        return [
            [new Serializable(), ["foo" => "bar"]],
            [['foo' => 'bar'], ["foo" => "bar"]],
            [[], []],
            [
                ['int' => 123, 'bool-true' => true, 'bool-false' => false, 'str' => 'string', 'null' => null],
                ["int" => 123, "bool-true" => true, "bool-false" => false, "str" => "string"]
            ]
        ];
    }

    /**
     * @return array
     */
    public function deserializeProvider(): array
    {
        $object1 = new \stdClass();
        $object1->foo = 'bar';

        $object2 = new \stdClass();
        $object2->{'int'} = 123;
        $object2->{'bool-true'} = true;
        $object2->{'bool-false'} = false;
        $object2->str = 'string';

        return [
            [["foo" => "bar"], null, $object1],
            [["foo" => "bar"], Serializable::class, new Serializable()],
            [[], null, new \stdClass()],
            [
                ["int" => 123, "bool-true" => true, "bool-false" => false, "str" => "string"],
                null,
                $object2
            ]
        ];
    }
}