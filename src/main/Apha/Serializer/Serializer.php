<?php
declare(strict_types = 1);

namespace Apha\Serializer;

interface Serializer
{
    /**
     * @param mixed $value
     * @return string
     */
    public function serialize($value): string;

    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function deserialize(string $data, string $type);
}