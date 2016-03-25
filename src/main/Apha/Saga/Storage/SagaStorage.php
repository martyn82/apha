<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

interface SagaStorage
{
    /**
     * @param string $sagaType
     * @param string $identity
     * @param array $associationValues
     * @param string $data
     * @return void
     */
    public function insert(string $sagaType, string $identity, array $associationValues, string $data);

    /**
     * @param string $sagaType
     * @param string $identity
     * @param array $associationValues
     * @param string $data
     * @return void
     */
    public function update(string $sagaType, string $identity, array $associationValues, string $data);

    /**
     * @param string $identity
     * @return void
     */
    public function delete(string $identity);

    /**
     * @param string $identity
     * @return string
     */
    public function findById(string $identity): string;

    /**
     * @param string $sagaType
     * @param array $associationValue
     * @return string[]
     */
    public function find(string $sagaType, array $associationValue): array;
}
