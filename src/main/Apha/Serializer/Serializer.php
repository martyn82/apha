<?php
declare(strict_types = 1);

namespace Apha\Serializer;

interface Serializer
{
    /**
     * @param mixed $value
     * @return mixed
     */
    public function serialize($value);

    /**
     * @param string $data
     * @param string $type
     * @return mixed
     */
    public function deserialize(string $data, string $type);
}