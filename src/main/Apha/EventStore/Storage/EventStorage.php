<?php
declare(strict_types = 1);

namespace Apha\EventStore\Storage;

use Apha\EventStore\EventDescriptor;

interface EventStorage
{
    /**
     * @param string $identity
     * @return bool
     */
    public function contains(string $identity): bool;

    /**
     * @param EventDescriptor $event
     * @return bool
     */
    public function append(EventDescriptor $event): bool;

    /**
     * @param string $identity
     * @return EventDescriptor[]
     */
    public function find(string $identity): array;

    /**
     * @return string[]
     */
    public function findIdentities(): array;
}