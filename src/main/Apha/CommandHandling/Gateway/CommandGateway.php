<?php
declare(strict_types = 1);

namespace Apha\CommandHandling\Gateway;

use Apha\Message\Command;

interface CommandGateway
{
    /**
     * @param Command $command
     * @return void
     */
    public function send(Command $command);
}