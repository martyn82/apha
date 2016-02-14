<?php
declare(strict_types = 1);

namespace Apha\Domain;

use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;

class Identity
{
    /**
     * @Serializer\Type("string")
     * @var string
     */
    private $value;

    /**
     * @return Identity
     */
    public static function createNew() : self
    {
        return new self(Uuid::uuid4()->toString());
    }

    /**
     * @param string $value
     * @return Identity
     */
    public static function fromString(string $value) : self
    {
        return new self($value);
    }

    /**
     * @param string $value
     */
    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getValue();
    }
}