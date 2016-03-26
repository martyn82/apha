<?php
declare(strict_types = 1);

namespace Apha\Domain;

use Apha\CommandHandling\AnnotatedCommandHandler;
use Apha\CommandHandling\CommandHandler;

abstract class AnnotatedAggregateRoot extends AggregateRoot implements CommandHandler
{
    use AnnotatedCommandHandler;
}
