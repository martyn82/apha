<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\Demo;

use Apha\Domain\Identity;
use Apha\Message\Event;

final class DemonstratedEvent extends Event
{
    /**
     * @param Identity $id
     */
    public function __construct(Identity $id)
    {
        $this->setIdentity($id);
    }
}
