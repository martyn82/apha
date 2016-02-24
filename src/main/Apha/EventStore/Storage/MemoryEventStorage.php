<?php
declare(strict_types = 1);

namespace Apha\EventStore\Storage;

use Apha\EventStore\EventDescriptor;

class MemoryEventStorage implements EventStorage
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @param string $identity
     * @return bool
     */
    public function contains(string $identity): bool
    {
        return array_key_exists($identity, $this->data);
    }

    /**
     * @param EventDescriptor $event
     * @return bool
     */
    public function append(EventDescriptor $event): bool
    {
        if (!$this->contains($event->getIdentity())) {
            $this->data[$event->getIdentity()] = [];
        }

        $this->data[$event->getIdentity()][] = $event->toArray();
        return true;
    }

    /**
     * @param string $identity
     * @return EventDescriptor[]
     */
    public function find(string $identity): array
    {
        if (!$this->contains($identity)) {
            return [];
        }

        return array_map(
            function (array $event): EventDescriptor {
                return EventDescriptor::reconstructFromArray($event);
            },
            $this->data[$identity]
        );
    }

    /**
     * @return string[]
     */
    public function findIdentities(): array
    {
        return array_keys($this->data);
    }
}