<?php
declare(strict_types = 1);

namespace Apha\Saga\Storage;

interface SagaStorageTest
{
    public function insertRegistersSaga();

    public function findRetrievesSagaByType();

    public function findRetrievesSagaByAssociationValues();

    public function findReturnsEmptyIfNoAssociationsAreFound();

    public function findByIdRetrievesSagaById();

    public function findByIdRetrievesEmptyResultIfSagaDoesNotExist();

    public function updateUpdatesSaga();

    public function updateInsertsSagaIfItDoesNotExist();

    public function deleteRemovesSaga();

    public function deleteIsIdempotent();
}
