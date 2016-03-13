<?php
declare(strict_types = 1);

namespace Apha\Repository;

use Apha\Domain\AggregateRoot;
use Apha\Domain\Identity;
use Apha\EventStore\ConcurrencyException;

interface Repository
{
    /**
     * @param AggregateRoot $aggregateRoot
     * @param int $expectedPlayhead
     * @return void
     * @throws ConcurrencyException
     */
    public function store(AggregateRoot $aggregateRoot, int $expectedPlayHead = -1);

    /**
     * @param Identity $aggregateIdentity
     * @return AggregateRoot
     */
    public function findById(Identity $aggregateIdentity): AggregateRoot;
}