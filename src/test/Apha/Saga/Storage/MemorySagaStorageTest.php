<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

/**
 * @group saga
 */
class MemorySagaStorageTest extends SagaStorageTest
{
    /**
     * @return SagaStorage
     */
    protected function createSagaStorage(): SagaStorage
    {
        return new MemorySagaStorage();
    }
}