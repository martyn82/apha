<?php
declare(strict_types = 1);

namespace Apha\Saga;

use JMS\Serializer\Annotation as Serializer;

class AssociationValue
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $key;

    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $value;

    /**
     * @param string $key
     * @param string $value
     */
    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}