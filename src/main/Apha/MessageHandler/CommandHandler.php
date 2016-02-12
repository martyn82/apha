<?php

namespace Apha\MessageHandler;

use Apha\Domain\Message\Command;

interface CommandHandler
{
    /**
     * @param Command $command
     * @return void
     */
    public function handle(Command $command);
}