<?php
declare(strict_types = 1);

namespace Apha\EventStore\Storage;

interface EventStorageTest
{
    public function containsReturnsFalseIfIdentityDoesNotExistInStorage();

    public function containsReturnsTrueIfIdentityExistsInStorage();

    public function appendAcceptsTheFirstEventForAnIdentity();

    public function appendAcceptsTheNextEventForAnIdentity();

    public function findWillReturnEmptyResultIfIdentityDoesNotExist();

    public function findWillReturnAllEventsInOrderForIdentity();

    public function findIdentitiesWillRetrieveAllKnownIdentities();
}