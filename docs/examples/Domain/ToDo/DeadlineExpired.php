<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\ToDo;

use Apha\Domain\Identity;
use Apha\Message\Event;

final class DeadlineExpired extends Event
{
    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->setIdentity($identity);
    }
}
