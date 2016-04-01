<?php
declare(strict_types = 1);

namespace Apha\Saga;

use Apha\Serializer\Serializer;

class SagaSerializer implements Serializer
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var SagaFactory
     */
    private $factory;

    /**
     * @param Serializer $serializer
     * @param SagaFactory $factory
     */
    public function __construct(Serializer $serializer, SagaFactory $factory)
    {
        $this->serializer = $serializer;
        $this->factory = $factory;
    }

    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value): string
    {
        return $this->serializer->serialize($value);
    }

    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function deserialize(string $data, string $type)
    {
        /* @var $deserialized Saga */
        $deserialized = $this->serializer->deserialize($data, $type);
        $this->factory->hydrate($deserialized);
        return $deserialized;
    }
}