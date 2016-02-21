<?php
declare(strict_types = 1);

namespace Apha\Serializer;

use JMS\Serializer\SerializerBuilder;

class JsonSerializer implements Serializer
{
    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private $serializer;

    /**
     */
    public function __construct()
    {
        $this->serializer = SerializerBuilder::create()->build();
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value) : string
    {
        return $this->serializer->serialize($value, 'json');
    }

    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function deserialize(string $data, string $type)
    {
        return $this->serializer->deserialize($data, $type, 'json');
    }
}