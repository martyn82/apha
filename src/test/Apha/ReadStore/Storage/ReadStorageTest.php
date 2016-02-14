<?php
declare(strict_types = 1);

namespace Apha\ReadStore\Storage;

interface ReadStorageTest
{
    public function upsertAddsDocumentToStorage();

    public function upsertUpdatesExistingDocument();

    public function deleteRemovesDocumentFromStorage();

    public function deleteIsIdempotent();

    public function findRetrievesDocumentByIdentity();

    public function findThrowsExceptionIfDocumentCannotBeFound();

    public function clearRemovesAllDocumentsFromStorage();

    public function clearIsIdempotent();

    public function findAllRetrievesAPageOfDocuments();

    public function findAllRetrievesEmptyArrayIfNoneFound();

    public function findByRetrievesAPageOfDocumentsMatchingCriteria();
}