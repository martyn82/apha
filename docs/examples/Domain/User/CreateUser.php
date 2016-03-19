<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\User;

use Apha\Domain\Identity;
use Apha\Message\Command;

final class CreateUser extends Command
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @param Identity $identity
     */
    public function __construct(Identity $identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return Identity
     */
    public function getId(): Identity
    {
        return $this->identity;
    }
}
