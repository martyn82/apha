<?php
declare(strict_types=1);

namespace Apha\ReadStore;

use Apha\Message\Event;

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
     * @return string
     */
    public function getId() : string;

    /**
     * @return int
     */
    public function getVersion() : int;

    /**
     * @param Event $event
     * @return void
     */
    public function apply(Event $event);
}