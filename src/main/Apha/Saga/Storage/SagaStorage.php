<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

use Apha\EventStore\EventDescriptor;

interface SagaStorage
{
    /**
     * @param string $sagaType
     * @param string $identity
     * @param array $associationValues
     * @return void
     */
    public function insert(string $sagaType, string $identity, array $associationValues);

    /**
     * @param string $sagaType
     * @param string $identity
     * @param array $associationValues
     * @param EventDescriptor[] $events
     * @return void
     */
    public function update(string $sagaType, string $identity, array $associationValues, array $events);

    /**
     * @param string $identity
     * @return void
     */
    public function delete(string $identity);

    /**
     * @param string $identity
     * @return array
     */
    public function findById(string $identity): array;

    /**
     * @param string $sagaType
     * @param array $associationValue
     * @return string[]
     */
    public function find(string $sagaType, array $associationValue): array;
}