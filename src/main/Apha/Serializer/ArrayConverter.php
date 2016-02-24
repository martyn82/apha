<?php
declare(strict_types = 1);

namespace Apha\Serializer;

class ArrayConverter
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     */
    public function __construct()
    {
        $this->serializer = new JsonSerializer();
    }

    /**
     * @param object $object
     * @return array
     */
    public function objectToArray($object): array
    {
        return json_decode(
            $this->serializer->serialize($object),
            $assoc = true
        );
    }

    /**
     * @param array $data
     * @param string $type [optional]
     * @return object
     */
    public function arrayToObject(array $data, string $type = null)
    {
        if ($type != null) {
            return $this->reconstructType($data, $type);
        }

        $result = json_decode(
            json_encode($data),
            $assoc = false
        );

        if (!is_object($result)) {
            return new \stdClass();
        }

        return $result;
    }

    /**
     * @param array $data
     * @param string $type
     * @return object
     */
    private function reconstructType(array $data, string $type)
    {
        return $this->serializer->deserialize(
            $this->serializer->serialize($data),
            $type
        );
    }
}