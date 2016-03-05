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
    private $type;

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
     * @param string $type
     * @param string $event
     * @param string $payload
     * @param int $playHead
     * @return EventDescriptor
     */
    public static function record(string $identity, string $type, string $event, string $payload, int $playHead): self
    {
        return new self(
            $identity,
            $type,
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
            $data['type'],
            $data['event'],
            $data['payload'],
            $data['recorded'],
            $data['playHead']
        );
    }

    /**
     * @param string $identity
     * @param string $type
     * @param string $event
     * @param string $payload
     * @param string $recorded
     * @param int $playHead
     */
    private function __construct(
        string $identity,
        string $type,
        string $event,
        string $payload,
        string $recorded,
        int $playHead
    )
    {
        $this->identity = $identity;
        $this->type = $type;
        $this->event = $event;
        $this->payload = $payload;
        $this->recorded = $recorded;
        $this->playHead = $playHead;
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * @return int
     */
    public function getPlayHead(): int
    {
        return $this->playHead;
    }

    /**
     * @return string
     */
    public function getRecorded(): string
    {
        return $this->recorded;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'identity' => $this->getIdentity(),
            'type' => $this->getType(),
            'event' => $this->getEvent(),
            'payload' => $this->getPayload(),
            'playHead' => $this->getPlayHead(),
            'recorded' => $this->getRecorded()
        ];
    }
}