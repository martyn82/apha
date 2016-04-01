<?php
declare(strict_types = 1);

namespace Apha\Examples\Domain\ToDo;

use Apha\Domain\Identity;
use Apha\Message\Command;

final class MarkItemDone extends Command
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
    public function getIdentity(): Identity
    {
        return $this->identity;
    }
}
