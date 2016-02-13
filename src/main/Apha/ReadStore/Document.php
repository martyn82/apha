<?php
declare(strict_types=1);

namespace Apha\ReadStore;

use Ramsey\Uuid\UuidInterface;

interface Document
{
    /**
     * @return array
     */
    public function serialize() : array;

    /**
     * @param array $serialized
     * @return Document
     */
    public function deserialize(array $serialized) : self;

    /**
     * @return UuidInterface
     */
    public function getId() : UuidInterface;

    /**
     * @return int
     */
    public function getVersion() : int;
}