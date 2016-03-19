<?php
declare(strict_types = 1);

namespace Apha\Examples\Projections;

use Apha\Examples\Domain\Demo\DemonstratedEvent;
use Apha\StateStore\Document;

class DemoDocument implements Document
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $version;

    /**
     * @var bool
     */
    private $demonstrated = false;

    /**
     * @param string $id
     * @param int $version
     */
    public function __construct(string $id, int $version)
    {
        $this->id = $id;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getVersion() : int
    {
        return $this->version;
    }

    /**
     * @param \Apha\Message\Event $event
     * @return void
     */
    public function apply(\Apha\Message\Event $event)
    {
        $this->version = $event->getVersion();

        if ($event instanceof DemonstratedEvent) {
            $this->demonstrated = true;
        }
    }
}
