<?php
declare(strict_types = 1);

namespace Apha\EventStore;

final class EventDescriptor
{
    /**
     * @var string
     */
    private $identity;

    /**
     * @var string
     */
    private $event;

    /**
     * @var string
     */
    private $payload;

    /**
     * @var string
     */
    private $recorded;

    /**
     * @var int
     */
    private $playHead;

    /**
     * @param string $identity
     * @param string $event
     * @param string $payload
     * @param int $playHead
     * @return EventDescriptor
     */
    public static function record(string $identity, string $event, string $payload, int $playHead): self
    {
        return new self(
            $identity,
            $event,
            $payload,
            date('r'),
            $playHead
        );
    }

    /**
     * @param array $data
     * @return EventDescriptor
     */
    public static function reconstructFromArray(array $data): self
    {
        return new self(
            $data['identity'],
            $data['event'],
            $data['payload'],
            $data['recorded'],
            $data['playHead']
        );
    }

    /**
     * @param string $identity
     * @param string $event
     * @param string $payload
     * @param string $recorded
     * @param int $playHead
     */
    private function __construct(string $identity, string $event, string $payload, string $recorded, int $playHead)
    {
        $this->identity = $identity;
        $this->event = $event;
        $this->payload = $payload;
        $this->recorded = $recorded;
        $this->playHead = $playHead;
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return int
     */
    public function getPlayHead()
    {
        return $this->playHead;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'identity' => $this->identity,
            'event' => $this->event,
            'payload' => $this->payload,
            'playHead' => $this->playHead,
            'recorded' => $this->recorded
        ];
    }
}