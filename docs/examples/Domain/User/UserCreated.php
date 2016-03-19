<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\User;

use Apha\Domain\Identity;

final class UserCreated extends \Apha\Message\Event
{
    /**
     * @param Identity $id
     */
    public function __construct(Identity $id)
    {
        $this->setIdentity($id);
    }
}
