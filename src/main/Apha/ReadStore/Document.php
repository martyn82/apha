<?php
declare(strict_types=1);

namespace Apha\ReadStore;

use Apha\Domain\Identity;

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
     * @return Identity
     */
    public function getId() : Identity;

    /**
     * @return int
     */
    public function getVersion() : int;
}